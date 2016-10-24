<?php

namespace Runalyze\Bundle\CoreBundle\Controller\Connect;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Runalyze\Profile\SyncProvider;

/**
 * Class TomTomMySportsController
 * @author Hannes Christiansen <hannes@runalyze.de>
 * @author Michael Pohl <michael@runalyze.de>
 * @package Runalyze\Bundle\CoreBundle\Controller\Connect
 */
class TomTomMySportsController extends Controller
{
    /**
     * @Route("/connect/tomtomMySports")
     */
    public function connectAction()
    {
        return $this->get('oauth2.registry')
            ->getClient('tomtomMySports')
            ->redirect();
    }
    
    /**
     * @Route("/connect/tomtomMySports/check", name="connect_tomtom_mysports_check")
     */
    public function connectCheckAction(Request $request)
    {
        $client = $this->get('oauth2.registry')->getClient('tomtomMySports');
        $accessToken = $client->getAccessToken();
        
        // $accessToken->getToken()  store AccessToken
        // $accessToken->getRefreshToken()  storeRefreshToken
        // $accessToken->getExpires()  storeExpirationDate (don't know if it is for Token or RefreshToken)
        //SyncProvider::TOMTOM_MYSPORTS;

    }
}
