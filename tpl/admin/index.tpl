<script language="JavaScript" type="text/javascript">
<!--
function func_disp( no ){
    var val = $('input[name*=\'question['+no+'][kind]\']:radio:checked').val();
    if (val == 'checkbox' || val == 'radio') {
        $('#optbox'+no).show();
    } else {
        $('#optbox'+no).hide();
    }
}
//-->
</script>

<div id="admin-contents" class="contents-main">
<form name="form1" id="form1" method="post" action="?">
<input type="hidden" name="<!--{$smarty.const.TRANSACTION_ID_NAME}-->" value="<!--{$transactionid}-->" />
<input type="hidden" name="mode" value="" />
<input type="hidden" name="questionnaire_id" value="" />
    <!--{* ▼登録テーブルここから *}-->
    <table>
        <tr>
            <th>稼働・非稼働<span class="attention"> *</span></th>
            <td>
                <!--{assign var=key value="work"}-->
                <span class="attention"><!--{$arrErr[$key]}--></span>
                <!--{html_radios name="$key" options=$arrWork selected=$arrForm[$key].value}-->
            </td>
        </tr>
        <tr>
            <th>タイトル<span class="attention"> *</span></th>
            <td>
                <!--{assign var=key value="title"}-->
                <span class="attention"><!--{$arrErr[$key]}--></span>
                <input type="text" name="<!--{$key}-->" size="60" class="box60" value="<!--{$arrForm[$key].value|h}-->" <!--{if $arrErr[$key]}-->style="background-color:<!--{$smarty.const.ERR_COLOR|h}-->"<!--{/if}--> />
                <span class="attention"> (上限<!--{$arrForm[$key].length|h}-->文字)</span>
            </td>
        </tr>    
        <tr>
            <th>内容<span class="attention"> *</span></th>
            <td>
                <!--{assign var=key value="contents"}-->
                <span class="attention"><!--{$arrErr[$key]}--></span>
                <textarea name="<!--{$key}-->" cols="60" rows="8" class="area60" <!--{if $arrErr[$key]}-->style="background-color:<!--{$smarty.const.ERR_COLOR|h}-->"<!--{/if}-->><!--{$arrForm[$key].value}--></textarea><br />
                <span class="attention"> (上限<!--{$arrForm[$key].length|h}-->文字)</span>
            </td>
        </tr>
    
        <!--{* 質問作成エリアここから *}-->
		<!--{section name=question loop=$cnt_question}-->
        <!--{assign var=index value=$smarty.section.question.index}-->
        <!--{assign var=key value="question"}-->
        <tr>
            <!--{assign var=val value="name"}-->
            <th>質問<!--{$smarty.section.question.iteration}-->
            <!--{if $smarty.section.question.iteration eq 1}--><span class="attention"> *</span><!--{/if}-->
            </th>
            <td>
                <span class="attention"><!--{$arrErr[$key][$index][$val]}--></span>
                <input type="text" name="<!--{$key}-->[<!--{$index}-->][<!--{$val}-->]" size="60" class="box60" 
                    value="<!--{if isset($arrForm[$key].value[$index][$val]|smarty:nodefaults)}--><!--{$arrForm[$key].value[$index][$val]|h}--><!--{/if}-->"
                    <!--{if $arrErr[$key][$index][$val]}-->style="background-color:<!--{$smarty.const.ERR_COLOR|h}-->"<!--{/if}--> />
                <span class="attention"> (上限<!--{$arrForm[$key].length|h}-->文字)</span>
            </td>
        </tr>    

        <tr>
            <th></th>
            <!--{assign var=val value="kind"}-->
            <td>
                <!--{if isset($arrForm[$key].value[$index][$val]|smarty:nodefaults)}-->
                    <!--{assign var=selected value=$arrForm[$key].value[$index][$val]|default:'0'}-->
                <!--{else}-->
                    <!--{assign var=selected value='0'}-->
                <!--{/if}-->
                <span class="attention"><!--{$arrErr[$key][$index][$val]}--></span>
                <!--{html_radios_ex name="`$key`[`$index`][`$val`]`" onClick="func_disp(`$index`)"
                    options=$arrQuestion selected=$selected}-->
            </td>
        </tr>
        <tr id="optbox<!--{$index}-->" class="optbox">
            <th></th>
            <!--{assign var=val value="opt"}-->
            <td>
                <span class="attention"><!--{$arrErr[$key][$index][$val]}--></span>
		        <!--{section name=options loop=$cnt_options}-->
                <!--{assign var=optionsindex value=$smarty.section.options.index}-->
                <label><!--{$smarty.section.options.iteration}-->:</label>
                <input type="text" name="<!--{$key}-->[<!--{$index}-->][<!--{$val}-->][<!--{$optionsindex}-->]" size="30" class="box30" 
                    value="<!--{if isset($arrForm[$key].value[$index][$val]|smarty:nodefaults) && isset($arrForm[$key].value[$index][$val][$optionsindex]|smarty:nodefaults)}--><!--{$arrForm[$key].value[$index][$val][$optionsindex]}--><!--{/if}-->"
                    <!--{if $arrErr[$key][$index][$val]}-->style="background-color:<!--{$smarty.const.ERR_COLOR|h}-->"<!--{/if}--> />
                <br />
                <!--{/section}-->
            </td>
        </tr>


        <!--{/section}-->
        <!--{* 質問作成エリアここまで *}-->

    </table>
    <!--{* ▲登録テーブルここまで *}-->

    <div class="btn-area">
        <ul>
            <li><a class="btn-action" href="javascript:;" onclick="fnModeSubmit('confirm','questionnaire_id','<!--{$arrForm.questionnaire_id.value|h}-->');"><span class="btn-next">この内容で登録する</span></a></li>
        </ul>
    </div>
</form>

    <h2>アンケート一覧</h2>
    <!--{* ▼一覧表示エリアここから *}-->
    <form name="move" id="move" method="post" action="?">
    <input type="hidden" name="<!--{$smarty.const.TRANSACTION_ID_NAME}-->" value="<!--{$transactionid}-->" />
    <input type="hidden" name="questionnaire_id" value="" />
    <table class="list">
        <colgroup width="10%">
        <colgroup width="15%">
        <colgroup width="45%">
        <colgroup width="10%">
        <colgroup width="10%">
        <colgroup width="10%">
        <tr>
            <th class="edit">編集</th>
            <th>日付</th>
            <th>タイトル</th>
            <th>ページ参照</th>
            <th>結果取得</th>
            <th class="delete">削除</th>
        </tr>
        <!--{section name=data loop=$arrContents}-->
        <!--{assign var=id value=$arrContents[data].questionnaire_id}-->
        <tr style="background:<!--{if $arrContents[data].questionnaire_id != $tpl_questionnaire_id}-->#ffffff<!--{else}--><!--{$smarty.const.SELECT_RGB}--><!--{/if}-->;" class="center">
            <td>
                <!--{if $arrContents[data].questionnaire_id != $tpl_questionnaire_id}-->
                <a href="#" onclick="fnModeSubmit('pre_edit', 'questionnaire_id', '<!--{$id|h}-->'); return false">編集</a>
                <!--{else}-->
                編集中
                <!--{/if}-->
            </td>
            <td><!--{$arrContents[data].create_date|date_format:"%Y/%m/%d"}--></td>
            <td class="left">
                <!--{$arrContents[data].title|h|nl2br}-->
            </td>
            <td><a href="#" >参照</a></td>
            <td><a href="#" onclick="fnModeSubmit('csv', 'questionnaire_id', '<!--{$id|h}-->'); return false">download</a></td>
            <td><a href="#" onclick="fnModeSubmit('delete', 'questionnaire_id', '<!--{$id|h}-->'); return false">削除</a></td>
        </tr>
        <!--{sectionelse}-->
        <tr class="center">
            <td colspan="6">現在データはありません。</td>
        </tr>
        <!--{/section}-->
    </table>
    </form>
    <!--{* ▲一覧表示エリアここまで *}-->

</div>

<script type="text/javascript">
<!--
	<!--{section name=question loop=$cnt_question}-->
		func_disp(<!--{$smarty.section.question.index}-->);
	<!--{/section}-->	
//-->
</script>
