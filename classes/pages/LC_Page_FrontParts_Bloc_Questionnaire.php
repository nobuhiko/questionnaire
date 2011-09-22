<?php
require_once CLASS_REALDIR . 'pages/frontparts/bloc/LC_Page_FrontParts_Bloc.php';

/**
 * アンケート登録クラス
 *
 * ajaxにて登録処理を行う
 *
 * todo 会員じゃない場合に何を入れさせるか
 * todo 同じ人が複数回登録出来てしまう問題
 *
 * @uses LC_Page_FrontParts_Bloc
 * @package
 * @version $id$
 * @copyright
 * @author Nobuhiko <http://nob-log.info/>
 * @license
 */
class LC_Page_FrontParts_Bloc_Questionnaire extends LC_Page_FrontParts_Bloc {

    /** プラグイン情報配列 (呼び出し元でセットする) */
    var $arrPluginInfo;

    /**
     * Page を初期化する.
     *
     * @return void
     */
    function init() {
        parent::init();
        // デバイス別に･･･
        //$this->setTplMainpage($this->bloc_items['tpl_path']);
    }

    /**
     * Page のプロセス.
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
        $questionnaire_id = $_GET['questionnaire_id'];
        // 改修次第でやり方かえる
        $this->arrQuestionnaire = $this->getQuestionnaire($questionnaire_id);

        $objFormParam   = new SC_FormParam_Ex();
        $this->lfInitParam($objFormParam, $this->arrQuestionnaire);
        $objFormParam->setParam($_POST);
        $this->arrForm  = $objFormParam->getHashArray();

        switch ($this->getMode()) {
        case 'complete':
            $arrErr = $this->lfCheckError($objFormParam);
            if(empty($arrErr)) {

                $this->lfRegistData($objFormParam, $questionnaire_id);

                $result["error"]        = 0;
                echo json_encode($result);
            } else {
                $result["error"]        = 1;
                $result["error_msgs"]   = $arrErr;
                echo json_encode($result);
            }
            exit;
        }
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
     * パラメータの初期化を行う
     * @param Object $objFormParam
     */
    function lfInitParam(&$objFormParam, $arrQuestionnaire){

        foreach ($arrQuestionnaire['question'] as $key => $questionnaire) {
            switch($questionnaire['kind']) {
            case 'text':
                $objFormParam->addParam($questionnaire['name'], 'answer'.$key, MTEXT_LEN, 'KVa', array("EXIST_CHECK", "MAX_LENGTH_CHECK"));
                break;
            case 'textarea':
                $objFormParam->addParam($questionnaire['name'], 'answer'.$key, LTEXT_LEN, 'KVa', array("EXIST_CHECK", "MAX_LENGTH_CHECK"));
                break;
            case 'checkbox':
                $objFormParam->addParam($questionnaire['name'], 'answer'.$key, INT_LEN, 'n', array("EXIST_CHECK", "MAX_LENGTH_CHECK"));
                break;
            case'radio':
                $objFormParam->addParam($questionnaire['name'], 'answer'.$key, INT_LEN, 'n', array("EXIST_CHECK", "MAX_LENGTH_CHECK"));
                break;
            }
        }
    }

    /**
     * 入力されたパラメータのエラーチェックを行う。
     * @param Object $objFormParam
     * @return Array エラー内容
     */
    function lfCheckError(&$objFormParam){
        $objErr     = new SC_CheckError_Ex($objFormParam->getHashArray());
        $objErr->arrErr = $objFormParam->checkError();
        return $objErr->arrErr;
    }


    function getQuestionnaire($questionnaire_id) {

        $objQuery   = SC_Query_Ex::getSingletonInstance();
        $col        = '*';
        $table      = 'dtb_questionnaire';
        $where      = 'del_flg = 0';
        $arrQuestionnaire = $objQuery->select($col, $table, $where);

        if (empty($arrQuestionnaire)) return array();

        // 1行目だけ取得
        $arrQuestionnaire = reset($arrQuestionnaire);
        $arrQuestionnaire['question'] = unserialize($arrQuestionnaire['question']);

        return $arrQuestionnaire;
    }


    function lfRegistData(&$objFormParam, $questionnaire_id) {
        $objCustomer = new SC_Customer_Ex();
        $objQuery =& SC_Query_Ex::getSingletonInstance();
        $objQuery->begin();

        $result_id                  = $objQuery->nextVal('dtb_questionnaire_result_id_id');
        $sqlval['customer_id']      = $objCustomer->getValue('customer_id');
        $sqlval['questionnaire_id'] = $questionnaire_id;
        $sqlval['result_id']        = $result_id;
        $sqlval['del_flg']          = '0';
        $sqlval['create_date']      = 'now()';

        $objQuery->insert("dtb_questionnaire_result", $sqlval);

        $arrResults = $objFormParam->getDbArray();
        $i = 0;
        foreach ($arrResults as $key => $result) {

            $sqlval = array();
            $sqlval['answer_id']        = $i;
            $sqlval['result_id']        = $result_id;
            $sqlval['answer']           = SC_Utils_Ex::sfMergeParamCheckBoxes($result);

            $objQuery->insert("dtb_questionnaire_result_answer", $sqlval);

            $i++;
        }
        $objQuery->commit();

    }


}
