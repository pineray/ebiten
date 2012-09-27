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

// {{{ requires
require_once CLASS_EX_REALDIR . 'page_extends/admin/LC_Page_Admin_Ex.php';
require_once PLUGIN_UPLOAD_REALDIR . 'Ebiten/classes/Model_Plugin_Ebiten.php';

/**
 * プラグイン の設定画面コントローラクラス.
 *
 * @package Ebiten
 * @author pineray
 * @version $Id: $
 */
class LC_Page_Plugin_Ebiten_Config extends LC_Page_Admin_Ex {
    public $device = NULL;
    public $templates = array();

    /**
     * 初期化.
     *
     * @return void
     */
    function init() {
        parent::init();
        $this->tpl_mainpage = PLUGIN_UPLOAD_REALDIR ."Ebiten/templates/config.tpl";
        $this->tpl_subtitle = "EBITEN";
    }

    /**
     * プロセス.
     *
     * @return void
     */
    function process() {
        $this->action();
        $this->sendResponse();
    }

    /**
     * Page のアクション.
     *
     * @return void
     */
    function action() {
        $objFormParam = new SC_FormParam_Ex();
        $this->lfInitParam($objFormParam);
        $objFormParam->setParam($_POST);
        $objFormParam->convParam();
        
        $this->device = $objFormParam->getValue('device', DEVICE_TYPE_PC);
        $tpl_default = $this->getTemplateName($this->device);
        $templates = $this->getAllTemplates($this->device);
        foreach ($templates as $key => $value) {
            if ($value['template_code'] == $tpl_default) {
                unset($templates[$key]);
                break;
            }
        }
        $this->templates = $templates;
        $arrForm = array();
        $arrForm['device'] = $this->device;
        
        switch ($this->getMode()) {
        case 'edit':
            $arrForm = $objFormParam->getHashArray();
            $this->arrErr = $this->checkError($objFormParam, $arrForm);
            // エラーなしの場合にはデータを更新
            if (count($this->arrErr) == 0) {
                $data = array();
                $data[$this->device]['status'] = $arrForm['status'];
                $data[$this->device]['per'] = $arrForm['per'];
                $data[$this->device]['tpl_value'] = $arrForm['tpl_value'];
                // テストケース
                $data[$this->device]['case'][0] = array();
                if ($arrForm['case_0_tpl_code'] != '') {
                    $data[$this->device]['case'][0]['tpl_code'] = $arrForm['case_0_tpl_code'];
                    $data[$this->device]['case'][0]['tpl_value'] = $arrForm['case_0_tpl_value'];
                } else {
                    $arrForm['case_0_tpl_value'] = '';
                }
                // データ更新
                if (Model_Plugin_Ebiten::update($data)) {
                    $this->tpl_onload = "alert('登録が完了しました。');";
                }
                else {
                    $this->tpl_onload = "alert('エラーが発生しました。');";
                }
            }
            break;
        default:
            // プラグイン情報を取得.
            $data = Model_Plugin_Ebiten::getSetting($this->device);
            $arrForm['status'] = $data['status'];
            $arrForm['per'] = $data['per'];
            $arrForm['tpl_value'] = $data['tpl_value'];

            $arrForm['case_0_tpl_code'] = $data['case'][0]['tpl_code'];
            $arrForm['case_0_tpl_value'] = $data['case'][0]['tpl_value'];
            break;
        }
        $this->arrForm = $arrForm;
        $this->setTemplate($this->tpl_mainpage);
    }

    /**
     * デストラクタ.
     *
     * @return void
     */
    function destroy() {
        parent::destroy();
    }
    
    /**
     * パラメーター情報の初期化
     *
     * @param object $objFormParam SC_FormParamインスタンス
     * @return void
     */
    function lfInitParam(&$objFormParam) {
        $objFormParam->addParam('端末種別', 'device', INT_LEN, 'n', array('SELECT_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK'));
        $objFormParam->addParam('ステータス', 'status', INT_LEN, 'n', array('EXIST_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK'));
        $objFormParam->addParam('実施割合', 'per', PERCENTAGE_LEN, 'n', array('SPTAB_CHECK','MAX_LENGTH_CHECK', 'NUM_CHECK'));
        $objFormParam->addParam('デフォルト出力コード', 'tpl_value');

        $objFormParam->addParam('テストケーステンプレート', 'case_0_tpl_code', STEXT_LEN, 'a', array('SPTAB_CHECK','MAX_LENGTH_CHECK', 'ALNUM_CHECK'));
        $objFormParam->addParam('テストケース出力コード', 'case_0_tpl_value');
    }

    /**
     * フォーム入力パラメーターのエラーチェック
     *
     * @param object $objFormParam SC_FormParamインスタンス
     * @param array $arrForm フォーム入力パラメーター配列
     * @return array エラー情報を格納した連想配列
     */
    function checkError(&$objFormParam, $arrForm) {
        $objErr = new SC_CheckError_Ex($arrForm);
        // 入力パラメーターチェック
        $arrErr = $objFormParam->checkError();
        // 稼働が選択されている場合は実施割合とテストケースの入力チェック
        if ($arrForm['status'] == 1) {
            $objErr->doFunc(array('実施割合', 'per'), array('EXIST_CHECK'));
            $objErr->doFunc(array('実施割合', 'per', 100), array('MAX_CHECK'));
            $objErr->doFunc(array('実施割合', 'per', 1), array('MIN_CHECK'));
            $objErr->doFunc(array('テストケーステンプレート', 'case_0_tpl_code'), array('SELECT_CHECK'));
        }

        return array_merge((array)$arrErr, (array)$objErr->arrErr);
    }

    /**
     * テンプレート情報を取得する.
     *
     * @param integer $device_type_id 端末種別ID
     * @return array テンプレート情報の配列
     */
    function getAllTemplates($device_type_id) {
        $objQuery =& SC_Query_Ex::getSingletonInstance();
        return $objQuery->select('*', 'dtb_templates', 'device_type_id = ?', array($device_type_id));
    }


    /**
     * テンプレート名を返す.
     *
     * @param integer $device_type_id 端末種別ID
     * @param boolean $isDefault デフォルトテンプレート名を返す場合 true
     * @return string テンプレート名
     */
    function getTemplateName($device_type_id, $isDefault = false) {
        switch ($device_type_id) {
            case DEVICE_TYPE_MOBILE:
                return $isDefault ? MOBILE_DEFAULT_TEMPLATE_NAME : MOBILE_TEMPLATE_NAME;

            case DEVICE_TYPE_SMARTPHONE:
                return $isDefault ? SMARTPHONE_DEFAULT_TEMPLATE_NAME : SMARTPHONE_TEMPLATE_NAME;

            case DEVICE_TYPE_PC:
            default:
                break;
        }
        return $isDefault ? DEFAULT_TEMPLATE_NAME : TEMPLATE_NAME;
    }
}
