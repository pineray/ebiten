<!--{*
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
 *}-->

<!--{include file="`$smarty.const.TEMPLATE_ADMIN_REALDIR`admin_popup_header.tpl"}-->

<h2><!--{$tpl_subtitle}--></h2>
<form name="form1" id="form1" method="post" action="<!--{$smarty.server.REQUEST_URI|h}-->">
<input type="hidden" name="<!--{$smarty.const.TRANSACTION_ID_NAME}-->" value="<!--{$transactionid}-->" />
<input type="hidden" name="mode" value="edit">

<table class="form">
    <tr>
        <th class="column">対象閲覧環境</th>
        <td>
            <!--{assign var=key value="device"}-->
            <!--{if $arrErr[$key] != ''}-->
            <span class="red"><!--{$arrErr[$key]}--></span>
            <!--{/if}-->
            <select name="<!--{$key}-->" id="form-device">
                <option value="<!--{$smarty.const.DEVICE_TYPE_PC}-->"<!--{if $arrForm[$key] == $smarty.const.DEVICE_TYPE_PC}--> selected="selected"<!--{/if}-->>PC</option>
                <option value="<!--{$smarty.const.DEVICE_TYPE_MOBILE}-->"<!--{if $arrForm[$key] == $smarty.const.DEVICE_TYPE_MOBILE}--> selected="selected"<!--{/if}-->>モバイル</option>
                <option value="<!--{$smarty.const.DEVICE_TYPE_SMARTPHONE}-->"<!--{if $arrForm[$key] == $smarty.const.DEVICE_TYPE_SMARTPHONE}--> selected="selected"<!--{/if}-->>スマートフォン</option>
            </select>
        </td>
    </tr>
</table>

<h3><!--{if $arrForm[$key] == $smarty.const.DEVICE_TYPE_MOBILE}-->モバイル<!--{elseif $arrForm[$key] == $smarty.const.DEVICE_TYPE_SMARTPHONE}-->スマートフォン<!--{else}-->PC<!--{/if}--></h3>
<!--{if count($templates) > 0}-->
<table class="form">
    <tr>
        <th class="column">ステータス<span class="red">※</span></th>
        <td>
            <!--{assign var=key value="status"}-->
            <!--{if $arrErr[$key] != ''}-->
            <span class="red"><!--{$arrErr[$key]}--></span>
            <!--{/if}-->
            <input type="radio" id="status-active" name="<!--{$key}-->" value="1" <!--{if $arrForm[$key] == "1"}-->checked<!--{/if}--> /><label for="status-active">稼働</label>
            <input type="radio" id="status-inactive" name="<!--{$key}-->" value="0" <!--{if $arrForm[$key] == "0"}-->checked<!--{/if}--> /><label for="status-inactive">停止</label>
        </td>
    </tr>
    <tr>
        <th class="column">実施割合</th>
        <td>
            閲覧ユーザー全体の何％に対してテストケースのテンプレートを表示するか決定して下さい。<br />
            <!--{assign var=key value="per"}-->
            <!--{if $arrErr[$key] != ''}-->
            <span class="red"><!--{$arrErr[$key]}--></span>
            <!--{/if}-->
            <input type="text" name="<!--{$key}-->" value="<!--{$arrForm[$key]}-->" size="3" maxlength="3" />％
        </td>
    </tr>
</table>

<h4>デフォルト</h4>
<table class="form">
    <tr>
        <th>出力コード</th>
    </tr>
    <tr>
        <td>
            デフォルトのテンプレートにSmartyコード「&lt;!--{$tpl_ebiten}--&gt;」を挿入すると、以下のテキストエリアに入力した内容を出力することができます。<br />
            <!--{assign var=key value="tpl_value"}-->
            <!--{if $arrErr[$key] != ''}-->
            <span class="red"><!--{$arrErr[$key]}--></span>
            <!--{/if}-->
            <textarea name="<!--{$key}-->" rows="5" style="width: 98%;"><!--{$arrForm[$key]|h|smarty:nodefaults}--></textarea>
        </td>
    </tr>
</table>

<!--{section loop=$max_forms name=case}-->
<h4>テストケース <!--{$smarty.section.case.iteration}--></h4>
<table>
    <tr><th>テンプレート</th></tr>
    <tr>
        <td>
            <!--{assign var=key value="case_`$smarty.section.case.index`_tpl_code"}-->
            <!--{if $arrErr[$key] != ''}-->
            <span class="red"><!--{$arrErr[$key]}--></span>
            <!--{/if}-->
            <select name="<!--{$key}-->">
                <option value="">選択して下さい</option>
                <!--{foreach from=$templates item=tpl}-->
                <option value="<!--{$tpl.template_code}-->"<!--{if $tpl.template_code == $arrForm[$key]}--> selected="selected"<!--{/if}-->><!--{$tpl.template_name|h}--></option>
                <!--{/foreach}-->
            </select>
        </td>
    </tr>
    <tr><th>出力コード</th></tr>
    <tr>
        <td>
            選択したテンプレートにSmartyコード「&lt;!--{$tpl_ebiten}--&gt;」を挿入すると、以下のテキストエリアに入力した内容を出力することができます。<br />
            <!--{assign var=key value="case_`$smarty.section.case.index`_tpl_value"}-->
            <!--{if $arrErr[$key] != ''}-->
            <span class="red"><!--{$arrErr[$key]}--></span>
            <!--{/if}-->
            <textarea name="<!--{$key}-->" rows="5" style="width: 98%;"><!--{$arrForm[$key]|h|smarty:nodefaults}--></textarea>
        </td>
    </tr>
</table>
<!--{/section}-->

<div class="btn-area">
    <ul>
        <li>
            <a class="btn-action" href="javascript:;" onclick="document.form1.submit();return false;"><span class="btn-next">この内容で登録する</span></a>
        </li>
    </ul>
</div>
<!--{else}-->
<table>
    <tr>
        <td>
            選択可能なテンプレートがありません。<br />
            まず先にテストケース用のテンプレートを追加して下さい。
        </td>
    </tr>
</table>
<!--{/if}-->

</form>

<script type="text/javascript">
$(function(){
    $('#form-device').change(function(){
        fnModeSubmit('change_device', '', '');
    });
});
</script>

<!--{include file="`$smarty.const.TEMPLATE_ADMIN_REALDIR`admin_popup_footer.tpl"}-->
