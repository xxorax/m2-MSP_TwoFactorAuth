<?php
/**
 * MageSpecialist
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@magespecialist.it so we can send you a copy immediately.
 *
 * @category   MSP
 * @package    MSP_TwoFactorAuth
 * @copyright  Copyright (c) 2017 Skeeller srl (http://www.magespecialist.it)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


namespace MSP\TwoFactorAuth\Observer;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MSP\TwoFactorAuth\Api\TfaInterface;

class ControllerActionPredispatchAdminhtml implements ObserverInterface
{
    /**
     * @var TfaInterface
     */
    private $tfa;

    /**
     * @var ActionFlag
     */
    private $actionFlag;

    /**
     * @var UrlInterface
     */
    private $url;

    public function __construct(
        TfaInterface $tfa,
        ActionFlag $actionFlag,
        UrlInterface $url
    ) {

        $this->tfa = $tfa;
        $this->actionFlag = $actionFlag;
        $this->url = $url;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var $controllerAction \Magento\Backend\App\AbstractAction */
        $controllerAction = $observer->getEvent()->getControllerAction();
        $fullActionName = $controllerAction->getRequest()->getFullActionName();

        if (in_array($fullActionName, [
            'adminhtml_auth_login',
            'adminhtml_auth_logout',

            'msp_twofactorauth_activate_index',
            'msp_twofactorauth_activate_qrcode',
            'msp_twofactorauth_activate_post',

            'msp_twofactorauth_auth_index',
            'msp_twofactorauth_auth_post',
            'msp_twofactorauth_auth_regenerate',
        ])) {
            return;
        }
        
        if ($this->tfa->getUserMustActivateTfa()) {
            $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
            $url = $this->url->getUrl('msp_twofactorauth/activate/index');
            $controllerAction->getResponse()->setRedirect($url);

        } else if ($this->tfa->getUserMustAuth()) {
            $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
            $url = $this->url->getUrl('msp_twofactorauth/auth/index');
            $controllerAction->getResponse()->setRedirect($url);
        }
    }
}
