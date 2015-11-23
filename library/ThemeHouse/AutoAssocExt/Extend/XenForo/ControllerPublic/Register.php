<?php

/**
 *
 * @see XenForo_ControllerPublic_Register
 */
class ThemeHouse_AutoAssocExt_Extend_XenForo_ControllerPublic_Register extends XFCP_ThemeHouse_AutoAssocExt_Extend_XenForo_ControllerPublic_Register
{

    protected function _getExternalRegisterFormResponse($viewName, $templateName, array $extraParams = array())
    {
        $registerTemplates = array(
            'register_facebook',
            'register_twitter',
            'register_google',
            'register_tumblr',
            'register_ee',
        );

        if (!empty($extraParams['existingUser'])) {
            if (in_array($templateName, $registerTemplates)) {
                /* @var $userExternalModel XenForo_Model_UserExternal */
                $userExternalModel = $this->_getUserExternalModel();

                $session = XenForo_Application::getSession();

                $userId = $extraParams['existingUser']['user_id'];
                $redirect = $extraParams['redirect'];

                switch ($templateName) {
                    case 'register_facebook':
                        $fbToken = $session->get('fbToken');

                        $fbUser = XenForo_Helper_Facebook::getUserInfo($fbToken);
                        if (empty($fbUser['id'])) {
                            return $this->responseError(
                                new XenForo_Phrase('error_occurred_while_connecting_with_facebook'));
                        }

                        $provider = 'facebook';
                        $providerKey = $fbUser['id'];
                        $extra = array(
                            'token' => $fbToken
                        );
                        XenForo_Helper_Facebook::setUidCookie($fbUser['id']);

                        XenForo_Application::getSession()->remove('loginRedirect');
                        XenForo_Application::getSession()->remove('fbToken');
                        break;
                    case 'register_twitter':
                        $accessToken = @unserialize($session->get('twitterAccessToken'));
                        $credentials = @unserialize($session->get('twitterCredentials'));

                        if (!$accessToken || !$credentials) {
                            return $this->responseError(new XenForo_Phrase('unexpected_error_occurred'));
                        }

                        $provider = 'twitter';
                        $providerKey = $credentials['id_str'];
                        $extra = array(
                            'token' => $accessToken->getToken(),
                            'secret' => $accessToken->getTokenSecret()
                        );

                        XenForo_Application::getSession()->remove('twitterAccessToken');
                        XenForo_Application::getSession()->remove('twitterCredentials');
                        break;
                    case 'register_google':
                        $credentials = $session->get('googleCredentials');

                        if (!$credentials) {
                            return $this->responseError(new XenForo_Phrase('unexpected_error_occurred'));
                        }

                        $provider = 'google';
                        $providerKey = $credentials['basic']['sub'];
                        $extra = $credentials['extra'];
                        break;
                    case 'register_tumblr':
                        $session = XenForo_Application::getSession();
                        $accessToken = @unserialize($session->get('tumblrAccessToken'));
                        $credentials = @unserialize($session->get('tumblrCredentials'));

                        if (!$accessToken || !$credentials) {
                            return $this->responseError(new XenForo_Phrase('unexpected_error_occurred'));
                        }

                        $provider = 'tumblr';
                        $providerKey = $credentials['response']['user']['name'];
                        $extra = array(
                            'token' => $accessToken->getToken(),
                            'secret' => $accessToken->getTokenSecret()
                        );
                        break;
                    case 'register_ee':
                        $provider = $extraParams['provider'];

                        switch ($provider) {
                            case 'battlenet':
                                $helper = $this->getHelper('ExternalExtended_Helper_BattleNet');
                                break;
                            case 'github':
                                $helper = $this->getHelper('ExternalExtended_Helper_GitHub');
                                break;
                            case 'linkedin':
                                $helper = $this->getHelper('ExternalExtended_Helper_LinkedIn');
                                break;
                            case 'live':
                                $helper = $this->getHelper('ExternalExtended_Helper_Live');
                                break;
                            case 'odnoklassniki':
                                $helper = $this->getHelper('ExternalExtended_Helper_Odnoklassniki');
                                break;
                            case 'soundcloud':
                                $helper = $this->getHelper('ExternalExtended_Helper_SoundCloud');
                                break;
                            case 'twitch':
                                $helper = $this->getHelper('ExternalExtended_Helper_Twitch');
                                break;
                            case 'vk':
                                $helper = $this->getHelper('ExternalExtended_Helper_VK');
                                break;
                            case 'strava':
                                $helper = $this->getHelper('ExternalExtended_Helper_Strava');
                                break;
                            case 'vimeo':
                                $helper = $this->getHelper('ExternalExtended_Helper_Vimeo');
                                break;
                        }

                        $eeToken = XenForo_Application::getSession()->get('eeToken');
                        $eeUser = $helper->getUserInfo($eeToken);

                        $providerKey = $eeUser[$helper->authUser];
                        $extra = $helper->getAssociation($eeToken, $eeUser);

                        XenForo_Application::getSession()->remove('loginRedirect');
                        XenForo_Application::getSession()->remove('eeToken');
                        break;
                }

                $userExternalModel->updateExternalAuthAssociation($provider, $providerKey, $userId, $extra);

                $visitor = XenForo_Visitor::setup($userId);
                XenForo_Application::getSession()->userLogin($userId, $visitor['password_date']);

                $this->_getUserModel()->setUserRememberCookie($userId);

                return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS,
                    XenForo_Link::buildPublicLink('index'));
            }
        }

        return parent::_getExternalRegisterFormResponse($viewName, $templateName, $extraParams);
    }
}