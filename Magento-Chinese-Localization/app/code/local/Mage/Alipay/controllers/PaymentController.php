<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Mage
 * @package    Mage_Alipay
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Alipay Payment Front Controller
 *
 * @category   Mage
 * @package    Mage_Alipay
 * @name       Mage_Alipay_PaymentController
 * @author	   Magento Core Team <core@magentocommerce.com>, Quadra Informatique - Nicolas Fischer <nicolas.fischer@quadra-informatique.fr>
 */

class Mage_Alipay_PaymentController extends Mage_Core_Controller_Front_Action
{
	/**
     * Order instance
     */
	protected $_order;

	/**
     *  Get order  获得定单
     *
     *  @param    none
     *  @return	  Mage_Sales_Model_Order
     */
	public function getOrder()
	{
		if ($this->_order == null) {
			$session = Mage::getSingleton('checkout/session');
			$this->_order = Mage::getModel('sales/order');
			$this->_order->loadByIncrementId($session->getLastRealOrderId());
		}
		return $this->_order;
	}

	/**
     * When a customer chooses Alipay on Checkout/Payment page
     *  支付入口
     */
	public function redirectAction()
	{
		$session = Mage::getSingleton('checkout/session');
		$session->setAlipayPaymentQuoteId($session->getQuoteId());

		$order = $this->getOrder();
		if (!$order->getId()) {                       //$order->getId()    get entity_id
			$this->norouteAction();
			return;
		}
		$order->addStatusToHistory(
		$order->getStatus(),
		Mage::helper('alipay')->__('客户被重定向到支付宝支付页面')
		);

		//exit(var_dump(sprintf('%.2f',$order->getShipping_amount())));
		$order->save();

		$this->getResponse()
		->setBody($this->getLayout()
		->createBlock('alipay/redirect')
		->setOrder($order)
		->toHtml());

		$session->unsQuoteId();
	}

	/**
	 *  Alipay response router
	 *
	 *  @param    none
	 *  @return	  void
	 */
	public function notifyAction()
	{
		$model = Mage::getModel('alipay/payment');
		if ($this->getRequest()->isPost()) {
			$postData = $this->getRequest()->getPost();
			$method   = 'post';
		
		} else if ($this->getRequest()->isGet()) {
			$postData = $this->getRequest()->getQuery();
			$method   = 'get';

		} else {
			$model->generateErrorResponse();
		};
		
		$this->logvarRS('notifyAction',$_POST,'pending_paypal',Mage::helper('alipay')->__('Payment accepted by Alipay'));
		
			$order = Mage::getModel('sales/order')
			->loadByIncrementId($postData['body']);
	
			if (!$order->getId()) {
				$model->generateErrorResponse();
			}
		if($postData['trade_status']=='WAIT_SELLER_SEND_GOODS'){
		  if($order->getStatus()!='processing'){
			$order->addStatusToHistory(
			$model->getConfigData('order_status_payment_accepted'),
			Mage::helper('alipay')->__('买家已付款，等待卖家发货')
			);
			$order->sendNewOrderEmail();
			$this->saveInvoice($order);
			$order->save();
			
			}
			$this->_redirect('checkout/onepage/success');
			echo 'success';
		}else if($postData['trade_status']=="WAIT_BUYER_PAY"){
		if($order->getStatus()!='wait_buyer_pay'){
			$order->addStatusToHistory(
			'wait_buyer_pay',
			Mage::helper('alipay')->__('等待买家付款')
			);
	
			$order->save();
			}
			echo 'success';
		}else if($postData['trade_status']=="SEND_GOODS"){
			$order->addStatusToHistory(
			'send_goods',
			Mage::helper('alipay')->__('卖家已发货')
			);

			$order->save();
			echo 'success';
		}else if($postData['trade_status']=="WAIT_BUYER_CONFIRM_GOODS"){
			$order->addStatusToHistory(
			'wait_buyer_confirm_goods',
			Mage::helper('alipay')->__('等待买家确认')
			);

			$order->save();
			echo 'success';
		}else if($postData['trade_status']=="TRADE_FINISHED"){
			$order->addStatusToHistory(
			'trade_finishen',
			Mage::helper('alipay')->__('订单完成')
			);

			$order->save();
			echo 'success';
		}
	}

	/**
     *  Save invoice for order
     *
     *  @param    Mage_Sales_Model_Order $order
     *  @return	  boolean Can save invoice or not
     */
	protected function saveInvoice(Mage_Sales_Model_Order $order)
	{
		if ($order->canInvoice()) {
			$convertor = Mage::getModel('sales/convert_order');
			$invoice = $convertor->toInvoice($order);
			foreach ($order->getAllItems() as $orderItem) {
				if (!$orderItem->getQtyToInvoice()) {
					continue;
				}
				$item = $convertor->itemToInvoiceItem($orderItem);
				$item->setQty($orderItem->getQtyToInvoice());
				$invoice->addItem($item);
			}
			$invoice->collectTotals();
			$invoice->register()->capture();
			Mage::getModel('core/resource_transaction')
			->addObject($invoice)
			->addObject($invoice->getOrder())
			->save();
			return true;
		}

		return false;
	}

	/**
	 *  Success payment page
	 *
	 *  @param    none
	 *  @return	  void
	 */
	public function successAction()
	{
		$session = Mage::getSingleton('checkout/session');
		$session->setQuoteId($session->getAlipayPaymentQuoteId());
		$session->unsAlipayPaymentQuoteId();

		$order = $this->getOrder();

		if (!$order->getId()) {
			$this->norouteAction();
			return;
		}

		$order->addStatusToHistory(
		$order->getStatus(),
		Mage::helper('alipay')->__('Customer successfully returned from Alipay')
		);
		$order->save();
		$this->_redirect('checkout/onepage/success');
	}

  public function loginAction()
	{
	  $alipaylogin    = Mage::getStoreConfig('payment/alipay_payment/alipaylogin');
	  if($alipaylogin == 1){
  		$this->getResponse()
  		->setBody($this->getLayout()
  		->createBlock('alipay/login')
  		->toHtml());
		}else{
		  $this->_redirect('customer/account/login');
    }
	}
	
	public function backAction()
	{
	  $notify = Mage::getModel('alipay/notify');
	  $verify_result = $notify->confirm($_GET);
	  $_GET['email'] = 'alipay_'.$_GET['email'];
	  if($verify_result){
	     if($this->checkVipExist($_GET['email'])){
	       $this->getResponse()->setBody($this->getLayout()->createBlock('alipay/loginbk')->toHtml());
       }else{
         $this->getResponse()->setBody($this->getLayout()->createBlock('alipay/loginpost')->toHtml());
       }
    }else{
      $this->_redirect('/');
    }
	}
  
  function checkVipExist($email)
  {
     $flag = true;
     $collection = Mage::getResourceModel('customer/customer_collection')
            ->addAttributeToSelect('email');
     $collection->load();
     foreach ($collection as $_customer){
       if($_customer['email']==$email){
         $flag = false;
         break;
       }
     }
     return $flag;
  }

	/**
	 *  Failure payment page
	 *
	 *  @param    none
	 *  @return	  void
	 */
	public function errorAction()
	{
		$session = Mage::getSingleton('checkout/session');
		$errorMsg = Mage::helper('alipay')->__(' There was an error occurred during paying process.');

		$order = $this->getOrder();

		if (!$order->getId()) {
			$this->norouteAction();
			return;
		}
		if ($order instanceof Mage_Sales_Model_Order && $order->getId()) {
			$order->addStatusToHistory(
			Mage_Sales_Model_Order::STATE_CANCELED,//$order->getStatus(),
			Mage::helper('alipay')->__('Customer returned from Alipay.') . $errorMsg
			);

			$order->save();
		}

		$this->loadLayout();
		$this->renderLayout();
		Mage::getSingleton('checkout/session')->unsLastRealOrderId();
	}
	
	/**
   * 接口调用记录
   *
   * 使用时注意日志存放位置(****)
   * 
   * @param        String      $function_name(调用方法名)
   * @param        String      $postData(调用或返回数据)
   * @param        String      $inout(操作方式)
   * @param        String      $msg(异常信息)
   *  
   * @access       public   
   */
	function logvarRS($function_name,$postData,$inout,$msg=''){
		//define( 'DS', DIRECTORY_SEPARATOR );
		$content = array(
		'function_name' => $function_name,
		$inout      	=> $postData,
		'msg'           => $msg
		);
		$_path = dirname(__FILE__).DS;
		ini_set('date.timezone','PRC');
		file_put_contents($_path.date('Ymd').'.txt',date('Y-m-d H:i:s')."\n".var_export($content,true)."\n"."\n",FILE_APPEND);
	}
}
