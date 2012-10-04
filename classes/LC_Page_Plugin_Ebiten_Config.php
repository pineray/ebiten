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
    public $settings = array();
    public $max_forms = 0;

    /**
     * 初期化.
     *
     * @return void
     */
    function init() {
        parent::init();
        $this->tpl_mainpage = PLUGIN_UPLOAD_REALDIR ."Ebiten/templates/config.tpl";
        $this->tpl_subtitle = "EBITEN";
        // プラグイン共通設定を取得.
        $this->settings = Model_Plugin_Ebiten::getSetting('general');
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
        $this->max_forms = ($this->settings['max_cases'] <= count($templates)) ? $this->settings['max_cases'] : count($templates);
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
                $case_index = 0;
                for ($i = 0; $i < $this->settings['max_cases']; $i++) {
                    if ($arrForm["case_{$i}_tpl_code"] != '') {
                        $data[$this->device]['case'][$case_index] = array();
                        $data[$this->device]['case'][$case_index]['tpl_code'] = $arrForm["case_{$i}_tpl_code"];
                        $data[$this->device]['case'][$case_index]['tpl_value'] = $arrForm["case_{$i}_tpl_value"];
                        $case_index++;
                    } else {
                        $arrForm["case_{$i}_tpl_value"] = '';
                    }
                }
                // データ更新
                if (Model_Plugin_Ebiten::update($data)) {
                    $this->tpl_onload = "alert('登録が完了しました。');";
                    // フォームのデフォルト値をセットする
                    $this->setFormDefault($arrForm);
                }
                else {
                    $this->tpl_onload = "alert('エラーが発生しました。');";
                }
            }
            break;
        default:
            // フォームのデフォルト値をセットする
            $this->setFormDefault($arrForm);
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

        for ($i = 0; $i < $this->settings['max_cases']; $i++) {
            $objFormParam->addParam("テストケーステンプレート{$i}", "case_{$i}_tpl_code", STEXT_LEN, 'a', array('SPTAB_CHECK','MAX_LENGTH_CHECK', 'ALNUM_CHECK'));
            $objFormParam->addParam("テストケース出力コード{$i}", "case_{$i}_tpl_value");
        }
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

            $case_selected = array();
            for ($i = 0; $i < $this->settings['max_cases']; $i++) {
                if (strlen($arrForm["case_{$i}_tpl_code"]) != 0) {
                    if (in_array($arrForm["case_{$i}_tpl_code"], $case_selected)) {
                        $arrErr["case_{$i}_tpl_code"] = '※ 重複して選択されているテンプレートがあります。<br />';
                    } else {
                        $case_selected[] = $arrForm["case_{$i}_tpl_code"];
                    }
                }
            }
            !count($case_selected) and $arrErr['case_0_tpl_code'] = '※ テストケーステンプレートが選択されていません。<br />';
        }

        return array_merge((array)$arrErr, (array)$objErr->arrErr);
    }

    /**
     * フォームのデフォルト値をセットする.
     * 
     * @param array $arrForm
     * @return void
     */
    function setFormDefault(&$arrForm) {
        // プラグイン情報を取得.
        $data = Model_Plugin_Ebiten::getSetting($this->device, TRUE);
        $arrForm['status'] = $data['status'];
        $arrForm['per'] = $data['per'];
        $arrForm['tpl_value'] = $data['tpl_value'];

        for ($i = 0; $i < $this->settings['max_cases']; $i++) {
            $arrForm["case_{$i}_tpl_code"] = $data['case'][$i]['tpl_code'];
            $arrForm["case_{$i}_tpl_value"] = $data['case'][$i]['tpl_value'];
        }
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
