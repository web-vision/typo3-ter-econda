<?php

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Plugin 'Econda Plugin' for the 'econda' extension.
 *
 * @author  Detlef Balzer, Edgar Gaiser <gaiser@econda.de>
 */
class tx_econda_pi1 extends tslib_menu
{
    var $prefixId = 'tx_econda_pi1'; // Same as class name
    var $scriptRelPath = 'pi1/class.tx_econda_pi1.php'; // Path to this script relative to the extension dir.
    var $extKey = 'econda'; // The extension key.
    //var $pi_checkCHash = TRUE;
    var $currency_factor = 1;
    var $eConf;

    /**
     * Insert some code into each website,
     * <a name="emos_name" title="content" ...
     * <script type="text/javascript" src="fileadmin/template/javascript/emos2.js"></script> at bottom of the content body
     */
    function main($sitecontent, $conf)
    {
 // get EMOS code and insert this code into each website
        require('class.tx_econda_emos.php');  // include ECONDA PHP-Helper library
        $this->conf = $conf;
        $http_host = t3lib_div::getIndpEnv('HTTP_HOST');
        $lang_id = t3lib_div::_GP('L');
        $this->pi_USER_INT_obj = 1;

        if(trim($conf['emospath']) != '' && stristr($conf['emospath'], 'plugin.econda') == false && stristr($conf['emospath'], 'plugin.tx_econda') == false) {
            $emosPath = ($conf['emospath']);
            $emos = new EMOS($emosPath); // make a new emos instance for this call
        } else {
            $emos = new EMOS('fileadmin/template/javascript/'); // make a new emos instance for this call
        }

        //$emos->debugMode(2);

        if(trim($conf['jstracking']) == 'true' && stristr($conf['jstracking'], 'plugin.econda') == false && stristr($conf['jstracking'], 'plugin.tx_econda') == false) {
            $emos->trackMode(2);
            $emos->addCdata();
        } else {
            $emos->addCdata();
        }
        if(trim($conf['jsstoptracking']) == 'true' && stristr($conf['jsstoptracking'], 'plugin.econda') == false && stristr($conf['jsstoptracking'], 'plugin.tx_econda') == false) {
            $emos->trackOnLoad(false);
        }

        $siteid = t3lib_div::_GP('id'); // read parameter id from url
        if($siteid == '' || (intval($siteid) == 0 && strlen($siteid) > 1)) {
            $siteid = $GLOBALS['TSFE']->page['uid']; // read from internal array if not found
        }        $ttproducts = t3lib_div::_GP('tx_ttproducts_pi1'); // read products parameter from url if exists
        $domainresult_row = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
            'uid',
            'sys_domain',
            'domainname like \'%'.$GLOBALS['TYPO3_DB']->quoteStr($http_host, 'sys_domain').'%\'',
            '',
            'uid',
            '1'
        );
        $domainresult_ary = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($domainresult_row); // get uid of current domain if exists
        $domainid = $domainresult_ary['uid'] ? $domainresult_ary['uid'] : 0;

        if(trim($conf['siteid']) != '' && stristr($conf['siteid'], 'plugin.econda') == false && stristr($conf['siteid'], 'plugin.tx_econda') == false) {
            $emos->addSiteID($conf['siteid']);
        } else {
            $emos->addSiteID($domainid);
        }
        if(trim($conf['maxperlevel']) != '' && stristr($conf['maxperlevel'], 'plugin.econda') == false && stristr($conf['maxperlevel'], 'plugin.tx_econda') == false) {
            $maxPerLevel = intval($conf['maxperlevel']);
        } else {
            $maxPerLevel = 64;
        }
        $temp_root_line = $GLOBALS['TSFE']->sys_page->getRootLine($siteid);
        $temp_root_line = array_reverse($temp_root_line); // array_shift reverses the array (rootline has numeric index in the wrong order!)
        $content = trim($GLOBALS['TSFE']->sys_page->getPathFromRootline($temp_root_line, $maxPerLevel)); // max. 64 characters per level
        $content = str_replace('/SHOP/SHOP', '/SHOP', $content);

        $pos = strrpos($content, '/');
        $temp_root_line = explode('/', $content);
        if(array_pop($temp_root_line) == array_pop($temp_root_line)) {
            $content = substr($content, 0, $pos); // do not list duplicate last identical entries
        }        if(substr($content, 0, 1) == '/') {
            $content = substr($content, 1);
        }

        if(trim($conf['content']) != '' && stristr($conf['content'], 'plugin.econda') == false && stristr($conf['content'], 'plugin.tx_econda') == false) {
            $emos->addContent($conf['content']);
        } else{
            $emos->addContent($content);
        }

        if($news = t3lib_div::_GP('tx_ttnews')) { // check, if current page is a news article
            if ($news['tt_news'] && t3lib_utility_Math::canBeInterpretedAsInteger($news['tt_news'])) { // if we know the news_id
                $news_row = $GLOBALS['TYPO3_DB']->exec_SELECTquery('title', 'tt_news', 'uid='.intval($news['tt_news']), '', '', '');
                $news_ary = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($news_row);
                $content .= '/'.$news_ary['title']; // we can add the news title, just got from database
            }
        }
        $countryid = 'DE';
        if(t3lib_div::_GP('tx_indexedsearch')) { // check, if current page is a search result
            //if (is_object($GLOBALS['TSFE'])){
              //$GLOBALS['TSFE']->set_no_cache();
            //}
            $tx_indexedsearch_ary = t3lib_div::_GP('tx_indexedsearch');
            $searchstring = $tx_indexedsearch_ary['sword'];

            $txidspf = false;
            if(isset($tx_indexedsearch_ary['pointer']) && isset($tx_indexedsearch_ary['_freeIndexUid'])) {
                $txidspf = true;
            }

            if(($tx_indexedsearch_ary['pointer'] == '0' && $tx_indexedsearch_ary['_freeIndexUid'] == '_') || $txidspf == false) {
                 $searchresult_row = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,hits', 'index_stat_search', 'searchstring='.$GLOBALS['TYPO3_DB']->fullQuoteStr($searchstring, 'index_stat_search'), '', 'uid desc', '1');
                 $searchresult_ary = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($searchresult_row); // get amount of hits from former last search,
                 $searchresult_hits = $searchresult_ary['hits'] ? $searchresult_ary['hits'] : 0; // a way to get an actual result
                 $emos->addSearch($searchstring, $searchresult_hits);
            }
        }

         /*
          * standard mailform
          */
        if (trim(t3lib_div::_GP('formtype_mail') != '')) {
            if(trim($conf['formname']) != '' && stristr($conf['formname'], 'plugin.econda') == false && stristr($conf['formname'], 'plugin.tx_econda') == false) {
                $emos->addContact($conf['formname']);
            } else {
                $emos->addContact('Mailform');
            }
        }

        if(trim($conf['langdebug']) == 'true' && stristr($conf['langdebug'], 'plugin.econda') == false && stristr($conf['langdebug'], 'plugin.tx_econda') == false) {
            // new handling of langid
            $countryid_ary = explode('.', $http_host);
            if ($countryid_ary[0] != $http_host) {
                $countryid = strtoupper(array_pop($countryid_ary));
            }
            $this->currency_factor = 1;
            $languageid = $lang_id;
            if(trim($languageid) == '') {
                $languageid = '0';
            }
        } else {
            if ($lang_id && t3lib_utility_Math::canBeInterpretedAsInteger($lang_id)) { // check for country code
                $countryid_row = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                    'a.tx_econda_static_country_isocode,a.tx_econda_static_currency_factor,b.lg_iso_2',
                    'sys_language a,static_languages b',
                    'a.uid='.intval($lang_id).' and a.static_lang_isocode=b.uid'
                );
                $countryid_ary = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($countryid_row);
                $countryid = $countryid_ary['tx_econda_static_country_isocode'];
                $this->currency_factor = $countryid_ary['tx_econda_static_currency_factor'];
                if ($this->currency_factor == '' || $this->currency_factor == 0) {
                    $this->currency_factor = 1;
                }
                $languageid = $countryid_ary['lg_iso_2'];
            } else {
                $countryid_ary = explode('.', $http_host);
                if ($countryid_ary[0] != $http_host) {
                    $countryid = strtoupper(array_pop($countryid_ary));
                }
                $this->currency_factor = 1;
                $languageid = 'DE';
            }
        }

        if(trim($conf['langid']) != '' && stristr($conf['langid'], 'plugin.econda') == false && stristr($conf['langid'], 'plugin.tx_econda') == false) {
            $emos->addLangID($conf['langid']);// language (ISO-2 code, eg. 'DE')
        } else {
            $emos->addLangID($languageid);
        }

        $emos->addCountryID($countryid); // country (ISO-2 code, eg. 'DE')
        $emos->addPageID(md5($languageid.'/'.$content));


        if ($siteid == $GLOBALS['TSFE']->tmpl->setup['plugin.']['tt_products.']['PIDbasket']) { // if site id of basket (module tt_products)
            $error = (t3lib_div::_GP('error')) ? 'error' : '';
            $recs = $GLOBALS['TSFE']->fe_user->getKey('ses', 'recs');
            $basketExt = $GLOBALS['TSFE']->fe_user->getKey('ses', 'basketExt');
            $oldBasketExt = $GLOBALS['TSFE']->fe_user->getKey('ses', 'oldBasketExt'); // get info stored by this ECONDA plugin
            $products_update = (t3lib_div::_GP('products_update') || t3lib_div::_GP('products_update_x')) ? true : false;
            if ($ttproducts['backPID'] == $siteid && !$products_update) { // no extra GET parameter to add a product
                if (t3lib_div::_GP('products_info') || t3lib_div::_GP('products_info_x')) {
                    $emos->addOrderProcess('2_Kundendaten', $error);
                }
                if (t3lib_div::_GP('products_payment') || t3lib_div::_GP('products_payment_x')) {
                    $emos->addOrderProcess('3_Zahlungsoptionen', $error);
                }
                if (t3lib_div::_GP('products_customized_payment') || t3lib_div::_GP('products_customized_payment_x')) {
                    $emos->addOrderProcess('3_Zahlungsoptionen', $error);
                }
                if (t3lib_div::_GP('products_overview') || t3lib_div::_GP('products_overview_x')) {
                    $emos->addOrderProcess('4_Bestelluebersicht', $error);
                }
                // if (t3lib_div::_GP('products_redeem_gift') || t3lib_div::_GP('products_redeem_gift_x')) {}
                // if (t3lib_div::_GP('products_clear_basket') || t3lib_div::_GP('products_clear_basket_x')) {}
                if (t3lib_div::_GP('products_finalize') || t3lib_div::_GP('products_finalize_x')) {
                    $emos->addOrderProcess('5_Bestaetigung', $error);
                    $first_name = $GLOBALS['TYPO3_DB']->fullQuoteStr($recs['personinfo']['first_name'], 'sys_products_orders');
                    $last_name = $GLOBALS['TYPO3_DB']->fullQuoteStr($recs['personinfo']['last_name'], 'sys_products_orders');
                    $order_row = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                        'uid,zip,city,country,amount',
                        'sys_products_orders',
                        'first_name='.$first_name.' and last_name='.$last_name.' and deleted=0 and status=1',
                        '',
                        'uid desc',
                        '1'
                    );
                    $order_ary = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($order_row); // get last order with known customer name, I do not know a better way to get order id
                    if ($order_ary['country'] == '') {
                        $order_ary['country'] = 'DE';
                        // if (strlen($order_ary['zip']) == 4) $order_ary['country'] = $countryid;
                    }
                    $zip = $GLOBALS['TYPO3_DB']->fullQuoteStr($order_ary['zip'], 'sys_products_orders');
                    $first_row = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                        'crdate,kundennr nr',
                        'sys_products_orders',
                        'first_name='.$first_name.' and last_name='.$last_name.' and deleted=0 and zip='.$zip,
                        '',
                        'uid',
                        '1'
                    );
                    $first_ary = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($first_row); // get first order of same customer to look for customer number
                    if ($first_ary['nr'] == '' || $first_ary['nr'] == '/') {
                        $first_ary['nr'] = $first_ary['crdate']; // use timestamp, if no customer number exists
                    }                    $emos->addEmosBillingPageArray(
                        $order_ary['uid'],
                        $first_ary['nr'],
                        $order_ary['amount'],
                        $order_ary['country'],
                        $order_ary['zip'],
                        $order_ary['city']
                    );
                    $basket = $this->productToEMOSBasket($order_ary['uid']);
                    $emos->addEmosBasketPageArray($basket);
                    $oldBasketExt = array (); // that is all, clear old basket, basket itself is clear at this time
                }
            } else {
                $emos->addOrderProcess('1_Warenkorb', $error);
            }
            if ($oldBasketExt) {
                foreach ($oldBasketExt as $id => $val) {
                    if ($val[';;;'] != $basketExt[$id][';;;']) { // found another amount for this product
                        if ($basketExt[$id][';;;']) { // product amount was changed
                            if ($basketExt[$id][';;;'] > $val[';;;']) { // amount is greater than before
                                $item = $this->productToEMOSItem($id, $basketExt[$id][';;;'] - $val[';;;']);
                                $emos->addToBasket($item);
                            } else { // amount is less than before
                                $item = $this->productToEMOSItem($id, $val[';;;'] - $basketExt[$id][';;;']);
                                $emos->removeFromBasket($item);
                            }
                        } else { // product does not anymore exist at basket
                            $item = $this->productToEMOSItem($id, $val[';;;']);
                            $emos->removeFromBasket($item);
                        }
                    }
                }
                $sizeofOldBasketExt = sizeof($oldBasketExt);
            } else {
                $sizeofOldBasketExt = 0;
            }
            if ($ttproducts && (sizeof($basketExt) > $sizeofOldBasketExt)) { // check, if current page is a add product page
                if ($ttproducts['product']) { // if we know the product_id, that was added
                    $item = $this->productToEMOSItem($ttproducts['product'], 1);
                    $emos->addToBasket($item);
                }
            }
            $GLOBALS['TSFE']->fe_user->setKey('ses', 'oldBasketExt', $basketExt);
        }
        if ($GLOBALS['TSFE']->page['module'] == 'shop') { // tt_products page
            if ($ttproducts) {
                if (t3lib_utility_Math::canBeInterpretedAsInteger($ttproducts['product'])) {
                    $item = $this->productToEMOSItem($ttproducts['product']);
                    $emos->addDetailView($item);
                }
            }
        }

        if(trim($conf['notracking']) == 'true' && stristr($conf['notracking'], 'plugin.econda') == false && stristr($conf['notracking'], 'plugin.tx_econda') == false) {
        } else {
            $retString = "\n<!-- Plugin: 018/tx_econda_pi1 [begin] -->\n";
            $retString .= $emos->toString();
            $retString .= "<!-- Plugin: 018/tx_econda_pi1 [end] -->\n";
            return $retString;
        }
    }

    /**
     * Convert a Product with given ID to an EMOS_Item
     */
    function productToEMOSItem($id, $quantity = 1)
    {
        $products_row = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
            'p.itemnumber,p.title p_title,p.price,c.title c_title',
            'tt_products p,tt_products_cat c',
            'p.category=c.uid and p.uid='.intval($id)
        );
        $products_ary = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($products_row);
        $item = new EMOS_Item(); // type definition see EMOS PHP-Helper class.tx_econda_pi1.php
        $item->productID = $products_ary['itemnumber'];
        $item->productName = $products_ary['p_title'];
        $item->price = number_format($products_ary['price'], 2);
        $item->productGroup = $products_ary['c_title'].($products_ary['p_title'] ? '/'.$products_ary['p_title'] : '');
        $item->quantity = $quantity;
        return $item;
    }

    /**
     * build an EMOS_Basket array
     */
    function productToEMOSBasket($uid)
    {
        $basket = array ();
        $order_item_row = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
            'sys_products_orders_qty,tt_products_uid',
            'sys_products_orders_mm_tt_products',
            'sys_products_orders_uid='.intval($uid)
        );
        $cnt = 0;
        while ($order_item_ary = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($order_item_row)) {
            if (t3lib_utility_Math::canBeInterpretedAsInteger($order_item_ary['tt_products_uid'])) {
                $item = $this->productToEMOSItem($order_item_ary['tt_products_uid'], $order_item_ary['sys_products_orders_qty']);
                $basket[$cnt] = $item;
                $cnt++;
            }
        }
        return $basket;
    }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/econda/pi1/class.tx_econda_pi1.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/econda/pi1/class.tx_econda_pi1.php']);
}
