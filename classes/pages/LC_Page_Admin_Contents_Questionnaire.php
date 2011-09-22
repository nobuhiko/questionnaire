<?php
// {{{ requires
require_once CLASS_EX_REALDIR . 'page_extends/admin/LC_Page_Admin_Ex.php';

/**
 * アンケート管理のページクラス
 *
 * @uses LC_Page_Admin_Ex
 * @package
 * @version $id$
 * @copyright
 * @author Nobuhiko <http://nob-log.info/>
 * @license
 */
class LC_Page_Admin_Contents_Questionnaire extends LC_Page_Admin_Ex {

    // }}}
    // {{{ functions

    /**
     * Page を初期化する.
     *
     * @return void
     */
    function init() {
        parent::init();
        $this->tpl_mainpage = $this->arrPluginInfo['fullpath'] . 'tpl/admin/index.tpl';
        $this->tpl_subnavi  = 'contents/subnavi.tpl';
        $this->tpl_subno    = 'questionnaire';
        $this->tpl_mainno   = 'contents';
        $this->tpl_subtitle = 'アンケート管理';
        $masterData         = new SC_DB_MasterData_Ex();
        $this->arrWork      = $masterData->getMasterData("mtb_work");

        // 質問数
        $this->cnt_question = 6;
        // checkbox上限数
        $this->cnt_options  = 8;
        // 質問種類
        $this->arrQuestion  = array(
            0           => '使用しない',
            'text'      => 'テキストボック',
            'textarea'  => 'テキストエリアス',
            'checkbox'  => 'チェックボックス',
            'radio'     => 'ラジオボタン');
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
     * デストラクタ.
     *
     * @return void
     */
    function destroy() {
        parent::destroy();
    }

    function action() {
        $objDb          = new SC_Helper_DB_Ex();
        $objFormParam   = new SC_FormParam_Ex();
        $this->lfInitParam($objFormParam);
        $objFormParam->setParam($_POST);
        $objFormParam->convParam();

        $this->tpl_questionnaire_id = $objFormParam->getValue('questionnaire_id');
        $mode           = $this->getMode();

        switch ($mode) {
        case 'csv':
            if (is_numeric($this->tpl_questionnaire_id)) {
                $this->lfDoCSV($this->tpl_questionnaire_id);
            }
            break;
        case 'delete':
            if (is_numeric($this->tpl_questionnaire_id)) {
                $objDb->sfDeleteRankRecord('dtb_questionnaire', 'questionnaire_id', $this->tpl_questionnaire_id);
                $this->objDisplay->reload();
            }

            break;
        case 'pre_edit':
            if (is_numeric($this->tpl_questionnaire_id)) {
                $arrContents = reset($this->lfGetContents($this->tpl_questionnaire_id));
                $arrContents['question'] = unserialize($arrContents['question']);
                $objFormParam->setParam($arrContents);
            }
            break;
        //---- 新規登録/編集登録
        case 'confirm':
            $this->arrErr = $this->lfCheckError($objFormParam);
            if (SC_Utils_Ex::isBlank($this->arrErr)) {
                // IDの値がPOSTされて来た場合は既存データの編集とみなす
                $member_id  = $_SESSION['member_id'];
                $this->lfRegistData($objFormParam, $member_id, $this->tpl_questionnaire_id);

                $this->tpl_onload = "window.alert('登録が完了しました');";
            }
            break;

        default:
            break;
        }

        $this->arrForm      = $objFormParam->getFormParamList();
        $this->arrContents  = $this->lfGetContents();
    }

    /**
     * 入力されたパラメータのエラーチェックを行う。
     * @param Object $objFormParam
     * @return Array エラー内容
     */
    function lfCheckError(&$objFormParam){
        $arrPost    = $objFormParam->getHashArray();
        $objErr     = new SC_CheckError_Ex($arrPost);
        $objErr->arrErr = $objFormParam->checkError();

        $this->checkQuestions($objErr, array('質問', 'question'));

        return $objErr->arrErr;
    }

    /**
     * パラメータの初期化を行う
     * @param Object $objFormParam
     */
    function lfInitParam(&$objFormParam){
        $objFormParam->addParam("ID", 'questionnaire_id', INT_LEN, 'n', array("NUM_CHECK", "MAX_LENGTH_CHECK"));
        $objFormParam->addParam("稼働・非稼働", 'work', INT_LEN, 'n', array("EXIST_CHECK", "NUM_CHECK", "MAX_LENGTH_CHECK"));
        $objFormParam->addParam("タイトル", 'title', MTEXT_LEN, 'KVa', array("EXIST_CHECK", "MAX_LENGTH_CHECK", "SPTAB_CHECK"));
        $objFormParam->addParam("内容", 'contents', LTEXT_LEN, 'KVa', array("MAX_LENGTH_CHECK"));
        $objFormParam->addParam("質問", 'question', MTEXT_LEN);
    }

    /**
     * アンケートを取得する
     *
     * @param mixed $questionnaire_id
     * @access public
     * @return array
     */
    function lfGetContents($questionnaire_id = null) {
        $objQuery =& SC_Query_Ex::getSingletonInstance();
        $col    = "*";
        $where  = "del_flg = 0";
        $arrVal = array();
        if ($questionnaire_id) {
            $where .= ' AND questionnaire_id = ?';
            $arrVal[] = $questionnaire_id;
        }

        $table  = "dtb_questionnaire";
        $objQuery->setOrder("rank DESC");
        $arrRet = $objQuery->select($col, $table, $where, $arrVal);

        return !empty($arrRet) ? $arrRet : array();
    }


    /**
     * データの登録を行う
     */
    function lfRegistData($objFormParam, $member_id, $questionnaire_id = null){

        $objQuery   =& SC_Query_Ex::getSingletonInstance();
        $sqlval     = $objFormParam->getHashArray();
        $sqlval['update_date'] = 'Now()';

        // serializeしちゃう
        $sqlval['question'] = $this->setQuestions($sqlval['question']);
        // 新規登録
        if($questionnaire_id == "") {
            // INSERTの実行
            $sqlval['creator_id']       = $member_id;
            $sqlval['rank']             = $objQuery->max('rank', "dtb_questionnaire") + 1;
            $sqlval['create_date']      = 'Now()';
            $sqlval['questionnaire_id'] = $objQuery->nextVal('dtb_questionnaire_questionnaire_id');
            $objQuery->insert("dtb_questionnaire", $sqlval);
        // 既存編集
        } else {
            $where = "questionnaire_id = ?";
            $objQuery->update("dtb_questionnaire", $sqlval, $where, array($questionnaire_id));
        }
    }


    /**
     * からの部分を削除してシリアライズする
     *
     * @param mixed $arrQuestion
     * @access public
     * @return void
     */
    function setQuestions($arrQuestion) {

        foreach($arrQuestion as $val) {

            if ($val['name'] == '') continue;
            if ($val['opt'][0] == '') {
                unset($val['opt']);
            } else {
                $val['opt'] = array_filter($val['opt']);
            }

            $arrValues[] = $val;
        }
        return serialize($arrValues);
    }

    /*
     *
     * 質問項目のエラーチェックをする
     *
     * name     0から順に入力されているかチェック
     * kind     name が入力されている場合は必ず入力されている
     * option   radio/checkbox の場合1つ以上入力必須
     *
     */
    function checkQuestions(&$objErr, $value) {
        $objErr->createParam($value);

        // 配列じゃない場合はエラー
        if (!is_array($objErr->arrParam[$value[1]])) {
            // 例外エラー
        } else {
            $max = count($objErr->arrParam[$value[1]]);
        }

        $blank = false;

        for($i = 0; $i < $max; $i++) {
            if(empty($objErr->arrParam[$value[1]][$i]['name'])) {
                // 質問の1行目が選択されていない
                if ($blank == false && $i == 0) {
                    $objErr->arrErr[$value[1]][$i]['name'] = "※ " . $value[0] . "は先頭の項目から順番に入力して下さい。<br />";
                }
                $blank = true;

            } else {
                if ($blank == true) {
                    // 質問が先頭から順番に入力されていない
                    $objErr->arrErr[$value[1]][$i]['name'] = "※ " . $value[0] . "は先頭の項目から順番に入力して下さい。<br />";
                } else {
                    self::_checkQuestionKind($objErr, $i, $value);
                }
            }
        }
    }


    /**
     * 質問の種類のエラーをチェックする
     *
     * @param mixed $objErr
     * @param mixed $value
     * @access protected
     * @return void
     */
    function _checkQuestionKind(&$objErr, $i, $value) {

        // 種類が選択されていない場合
        if(empty($objErr->arrParam[$value[1]][$i]['kind'])) {
            $objErr->arrErr[$value[1]][$i]['kind'] = "※ " . $value[0] . "の種類は" . $value[0] . "とセットで入力して下さい。<br />";

        } elseif ($objErr->arrParam[$value[1]][$i]['kind'] == 'checkbox'
            || $objErr->arrParam[$value[1]][$i]['kind'] == 'radio') {

                self::_checkQuestionOpt($objErr, $i, $value);
        }
    }


    /**
     * チェックボックスとラジオボタンの場合、内容が入力されているかチェックする
     *
     * @access protected
     * @return void
     */
    function _checkQuestionOpt(&$objErr, $i, $value) {

        // opt未入力チェック
        if (empty($objErr->arrParam[$value[1]][$i]['opt'])) {
            $objErr->arrErr[$value[1]][$i]['opt'] = "※ " . $value[0] . "の内容を入力して下さい。<br />";
        } else {
            // 順番に項目が埋まっているかのチェックどうしよう
        }
    }


    /**
     * dtb_questionnaire_results
     *
     * @param mixed $questionnaire_id
     * @access public
     * @return void
     */
    function lfDoCSV($questionnaire_id) {

        $arrHeader = array(
                            1 => '結果ID',
                            2 => '顧客ID',
                            3 => '回答日');

        $objQuery   =& SC_Query_Ex::getSingletonInstance();
        $arrResult  = $objQuery->select("result_id, customer_id, create_date", "dtb_questionnaire_result", "questionnaire_id = ?", array($questionnaire_id));

        foreach ($arrResult as $result) {

            $arrAns = $objQuery->select("answer_id, answer", "dtb_questionnaire_result_answer", "result_id = ?", array($result['result_id']));

            $count = count($arrAns);
            for($cnt = 0; $cnt < $count; $cnt++) {
                $key = 'answer' . $arrAns[$cnt]['answer_id'];
                $val = $arrAns[$cnt]['answer'];
                $arrRet[$key] = $val;
            }
            $arrCsv[] = array_merge($result, $arrRet);
        }

        for($cnt = 1; $cnt <= $count; $cnt++) {
            $arrHeader[] = '回答'.$cnt;
        }

        // header
        array_unshift($arrCsv, $arrHeader);

        // csv出力
        require_once CLASS_EX_REALDIR . 'helper_extends/SC_Helper_CSV_Ex.php';
        $objCSV = new SC_Helper_CSV_Ex();
        $objCSV->lfDownloadCsv($arrCsv);
        exit();
    }


}
