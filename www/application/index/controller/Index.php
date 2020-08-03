<?php
namespace app\index\controller;

use think\Controller;
use wxpay\JsApiPay;
use wxpay\lib\WxPayApi;
use wxpay\lib\WxPayUnifiedOrder;
use wxpay\WxPayConfig;
use wxpay\PayNotifyCallBack;
use think\facade\Log;

class Index extends Controller
{
	public function index()
	{
		return $this->fetch();
	}
	//打印输出数组信息
	function printf_info($data)
	{
		$info = '';
	    foreach($data as $key=>$value){
	        $info .= "<font color='#00ff55;'>$key</font> :  ".htmlspecialchars($value, ENT_QUOTES)." <br/>";
	    }
	    return $info;
	}

    public function jsapi()
    {
    	//①、获取用户openid
		try{

			$tools = new JsApiPay();
			$openId = $tools->GetOpenid();

			//②、统一下单
			$input = new WxPayUnifiedOrder();
			$input->SetBody("test");
			$input->SetAttach("test");
			$input->SetOut_trade_no("sdkphp".date("YmdHis"));
			$input->SetTotal_fee("1");
			$input->SetTime_start(date("YmdHis"));
			$input->SetTime_expire(date("YmdHis", time() + 600));
			$input->SetGoods_tag("test");
			// $input->SetNotify_url("https://test/index/index/notify.html");
			$input->SetTrade_type("JSAPI");
			$input->SetOpenid($openId);
			$config = new WxPayConfig();
			$order = WxPayApi::unifiedOrder($config, $input);
			$info = '<font color="#f00"><b>统一下单支付单信息</b></font><br/>';
			$info .= $this->printf_info($order);
			$jsApiParameters = $tools->GetJsApiParameters($order);
			Log::info($jsApiParameters);
			$this->assign('info', $info);
			$this->assign('jsApiParameters', $jsApiParameters);
			return $this->fetch();
		} catch(Exception $e) {
			Log::error(json_encode($e));
		}
		//③、在支持成功回调通知中处理成功之后的事宜，见 notify.php
		/**
		 * 注意：
		 * 1、当你的回调地址不可访问的时候，回调通知会失败，可以通过查询订单来确认支付是否成功
		 * 2、jsapi支付时需要填入用户openid，WxPay.JsApiPay.php中有获取openid流程 （文档可以参考微信公众平台“网页授权接口”，
		 * 参考http://mp.weixin.qq.com/wiki/17/c0f37d5704f0b64713d5d2c37b468d75.html）
		 */
    }

    public function notify()
    {
		$config = new WxPayConfig();
		Log::debug("begin notify");
		$notify = new PayNotifyCallBack();
		$notify->Handle($config, false);
    }
}
