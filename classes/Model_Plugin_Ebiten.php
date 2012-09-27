<?php
/*
 * Ebiten - Easy A/B Tester
 * Copyright (C) 2012 pineray. All Rights Reserved.
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/**
 * プラグイン のモデルクラス.
 *
 * @package Ebiten
 * @author pineray
 * @version $Id: $
 */
class Model_Plugin_Ebiten {

    private static $_settings = false;

    public static function forge() {
        // プラグイン情報を取得.
        $data = SC_Plugin_Util_Ex::getPluginByPluginCode("Ebiten");

        $data[DEVICE_TYPE_PC]           = unserialize($data['free_field1']);
        unset($data['free_field1']);
        $data[DEVICE_TYPE_SMARTPHONE]   = unserialize($data['free_field2']);
        unset($data['free_field2']);
        $data[DEVICE_TYPE_MOBILE]       = unserialize($data['free_field3']);
        unset($data['free_field3']);
        $data['general']                = unserialize($data['free_field4']);
        unset($data['free_field4']);
        Model_Plugin_Ebiten::$_settings = $data;
    }

    /**
     * 設定情報を返す.
     * 
     * @param string $key
     * @return array
     */
    public static function getSetting($key) {
        !Model_Plugin_Ebiten::$_settings and Model_Plugin_Ebiten::forge();
        return isset(Model_Plugin_Ebiten::$_settings[$key]) ? Model_Plugin_Ebiten::$_settings[$key] : FALSE;
    }

    /**
     * プラグイン情報の更新.
     * 
     * @param array $data
     * @return boolean
     */
    public static function update($data = array()) {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $sqlval = array();
        foreach ($data as $key => $value) {
            switch ($key) {
                case DEVICE_TYPE_PC:
                    $sqlval['free_field1'] = serialize($value);
                    break;
                case DEVICE_TYPE_SMARTPHONE:
                    $sqlval['free_field2'] = serialize($value);
                    break;
                case DEVICE_TYPE_MOBILE:
                    $sqlval['free_field3'] = serialize($value);
                    break;
                case 'general':
                    $sqlval['free_field4'] = serialize($value);
                    break;
                default:
                    break;
            }
        }
        $sqlval['update_date'] = 'CURRENT_TIMESTAMP';
        $where = "plugin_code = ?";
        // UPDATEの実行
        return $objQuery->update('dtb_plugin', $sqlval, $where, array(Model_Plugin_Ebiten::getSetting('plugin_code')));
    }

    /**
     * セッションに記録されているテストケースを判別し
     * 適切でなければ新たにセットする.
     * 
     * @param integer $device_type_id
     * @return string
     */
    public static function sessionVerify($device_type_id = FALSE) {
        !$device_type_id and $device_type_id = SC_Display_Ex::detectDevice();
        $setting_device = Model_Plugin_Ebiten::getSetting($device_type_id);
        if ($setting_device['status']) {
            if (!isset($_SESSION['ebiten']) || !in_array($_SESSION['ebiten'], array('default','case_0'))) {
                $rnd = rand(0, 100);
                if ($rnd <= $setting_device['per']) {
                    $_SESSION['ebiten'] = 'case_0';
                } else {
                    $_SESSION['ebiten'] = 'default';
                }
            }
        } else {
            // デフォルトの値をセッションに格納
            $_SESSION['ebiten'] = 'default';
        }

        return $_SESSION['ebiten'];
    }

    /**
     * 適用されているテストケースのテンプレートコードを返す.
     * 
     * @param integer $device_type_id
     * @return string
     */
    public static function getTplCode($device_type_id = FALSE) {
        !$device_type_id and $device_type_id = SC_Display_Ex::detectDevice();
        $case =  Model_Plugin_Ebiten::sessionVerify();
        $tpl_code = '';

        if ($case == 'case_0') {
            $setting = Model_Plugin_Ebiten::getSetting($device_type_id);
            $tpl_code = $setting['case'][0]['tpl_code'];
        } else {
            switch ($device_type_id) {
                case DEVICE_TYPE_MOBILE:
                    $tpl_code = MOBILE_TEMPLATE_NAME;
                    break;
                case DEVICE_TYPE_SMARTPHONE:
                    $tpl_code = SMARTPHONE_TEMPLATE_NAME;
                    break;
                case DEVICE_TYPE_PC:
                default:
                    $tpl_code = TEMPLATE_NAME;
                    break;
            }
        }
        
        return $tpl_code;
    }

    /**
     * 出力コードを返す.
     * 
     * @param integer $device_type_id
     * @return string
     */
    public static function getTplValue($device_type_id = FALSE) {
        !$device_type_id and $device_type_id = SC_Display_Ex::detectDevice();
        $setting = Model_Plugin_Ebiten::getSetting($device_type_id);
        return ($_SESSION['ebiten'] === 'default') ? $setting['tpl_value'] : $setting['case'][0]['tpl_value'];
    }

    /**
     * 引数のテンプレートコードがテストケースで指定されていれば
     * その閲覧環境でのテストを停止する.
     * 
     * @param integer $device_type_id
     * @param string $tpl_code
     * @return void
     */
    public static function disableTpl($device_type_id, $tpl_code) {
        $setting = Model_Plugin_Ebiten::getSetting($device_type_id);
        if ($setting['status'] == 0 || !isset($setting['case'][0]['tpl_code']) || $setting['case'][0]['tpl_code'] != $tpl_code) {
            return;
        } else {
            $setting['status'] = 0;
            Model_Plugin_Ebiten::update(array($device_type_id => $setting));
        }
    }
}
