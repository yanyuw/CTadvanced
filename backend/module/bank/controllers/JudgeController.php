<?php

namespace backend\module\bank\controllers;

use yii\web\Controller;
use yii\common\models\judge;
use yii\db\Query;

/**
 * Default controller for the `bank` module
 * 判断题：固定模式，一个题干，一个答案
 */
class JudgeController extends Controller
{
    public function actionIndex()
    {
        return "bank-judge"; // TODO: Change the autogenerated stub
    }
    /*
     * 判断题
     */

    /**
     * judge model
     * @property integer $jqid
     * @property string $jqitem
     * @property string $jqans
     * @property string $jqtail
     * @property string $jqrem
     * @property string $jqstatus
     */
    /*
     * 查找全部的判断题
     * 标志：flag
     * 1:全部的判断题
     * 2：有效的判断题
     * 3：模糊查找某题
     * 4：无效的判断题
     */
    public function actionQueryjudge()
    {
        $request = \Yii::$app->request;
        $flag = $request->post('flag');
        if ($flag == 1) {
            $query = (new Query())
                ->select("*")
                ->from('judge')
                ->all();
            return array("data" => $query, "msg" => "全部的判断题");
        } else if ($flag == 2) {
            $query = (new Query())
                ->select("*")
                ->from('judge')
                ->where(['jqstatus' => 1])
                ->all();
            return array("data" => $query, "msg" => "有效的判断题");
        } else if ($flag == 3) {
            $name = $request->post('name');
            $query = (new Query())
                ->select("*")
                ->from('judge')
                ->where(['or',
                    ['like', 'jqitem', $name],
                    ['like', 'jqans', $name],
                    ['like', 'jqtail', $name],
                    ['like', 'jqrem', $name],
                ])
                ->all();
            return array("data" => $query, "msg" => $name . "判断题");
        } else if ($flag == 4) {
            $query = (new Query())
                ->select("*")
                ->from('judge')
                ->where(['jqstatus' => 0])
                ->all();
            return array("data" => $query, "msg" => "无效的判断题");
        } else {
            return array("data" => $flag, "msg" => "输入错误");
        }
    }

    /*
     * 增加判断题：参数(题干、答案、详解、相关知识)
     * 选择：设置为四个选项，固定的模式
     */
    public function actionAddjudge()
    {
        $id = (new Query())
            ->select("*")
            ->from('judge')
            ->max('jqid');
        $id = $id + 1;
        $request = \Yii::$app->request;
        $item = $request->post('qitem');
        $ans = $request->post('ans');
        $tail = $request->post('tail');
        $rem = $request->post('rem');
        $query = (new Query())
            ->select('*')
            ->from('judge')
            ->where(['jqitem' => $item])
            ->one();
        if ($query) {
            return array("data" => $query, "msg" => "该题已在题库中，请勿重复添加");
        } else {
            $updatec = \Yii::$app->db->createCommand()->insert('judge',
                array('jqid' => $id, 'jqitem' => $item, 'jqans' => $ans, 'jqtail' => $tail,
                    'jqrem' => $rem, 'jqstatus' => 1))->execute();
            if ($updatec) {
                return array("data" => $updatec, "msg" => "插入判断题成功");
            } else {
                return array("data" => $updatec, "msg" => "插入失败，该题已插入");
            }
        }
    }

    /*
     * 删除判断题：一个函数实现
     * 给出标志：flag
     * 1:暂时删除
     * 2：永久删除
     * 实际的修改需删除变量
     */
    public function actionDelete()
    {
        $requset = \Yii::$app->request;
        $id = $requset->post('fid');
        $query = (new Query())
            ->select('*')
            ->from('judge')
            ->where(['jqid' => $id])
            ->one();
        if ($query) {
            $flag = $requset->post('flag');
            if ($flag == 1) {
                //暂时删除
                $updatec = \Yii::$app->db->createCommand()->update('judge', ['jqstatus' => 0], "jqid={$id}")->execute();
                if ($updatec) {
                    return array("data" => [$query, $updatec], "msg" => "该判断题删除成功");
                } else {
                    return array("data" => [$query, $updatec], "msg" => "该判断题已删除，不用重复删除");
                }
            } else if ($flag == 2) {
                //永久删除
                $updatec = \Yii::$app->db->createCommand()->delete('judge', ['jqid' => $id])->execute();
                if ($updatec) {
                    return array("data" => [$query, $updatec], "msg" => "该判断题永久删除成功");
                } else {
                    return array("data" => [$query, $updatec], "msg" => "该判断题已永久删除，不用重复删除");
                }
            } else {
                return array("data" => [$query, $flag], "msg" => "输入错误");
            }

        } else {
            return array("data" => $query, "msg" => "没有找到该判断题");
        }
    }

    /*
     * 修改判断题相关内容：一个函数实现
     * 给出标志：flag
     * 1:题干
     * 2：正确选项
     * 3：详解
     * 4：相关知识推荐
     * 5：状态
     * 实际的修改需删除变量
     */
    public function actionChange()
    {
        $request = \Yii::$app->request;
        $id = $request->post('cid');
        $query = (new Query())
            ->select('*')
            ->from('judge')
            ->where(['jqid' => $id])
            ->one();
        if ($query) {
            $flag = $request->post('flag');
            if ($flag == 1) {
                //题干
                $item = $request->post('item');
                if ($item == $query['jqitem']) {
                    return array("data" => [$query, $item], "msg" => "两次题干一致，不能修改");
                } else {
                    $updatec = \Yii::$app->db->createCommand()->update('judge', ['jqitem' => $item], "jqid={$id}")->execute();
                    if ($updatec) {
                        return array("data" => [$query, $item, $updatec], "msg" => "该判断题题干修改成功");
                    } else {
                        return array("data" => [$query, $item, $updatec], "msg" => "该判断题题干已修改，不用重复修改");
                    }
                }
            }
            else if ($flag == 2) {
                //正确答案
                $ans = $request->post('ans');
                if ($ans == $query['jqans']) {
                    return array("data" => [$query, $ans], "msg" => "两次答案一致，不能修改");
                } else {
                    $updatec = \Yii::$app->db->createCommand()->update('judge', ['jqans' => $ans], "jqid={$id}")->execute();
                    if ($updatec) {
                        return array("data" => [$query, $ans, $updatec], "msg" => "该判断题答案修改成功");
                    } else {
                        return array("data" => [$query, $ans, $updatec], "msg" => "该判断题答案已修改，不用重复修改");
                    }
                }
            }
            else if ($flag == 3) {
                //详解
                $tail = $request->post('tail');
                if ($tail == $query['jqtail']) {
                    return array("data" => [$query, $tail], "msg" => "两次详解一致，不能修改");
                } else {
                    $updatec = \Yii::$app->db->createCommand()->update('judge', ['jqtail' => $tail], "jqid={$id}")->execute();
                    if ($updatec) {
                        return array("data" => [$query, $tail, $updatec], "msg" => "该判断题详解修改成功");
                    } else {
                        return array("data" => [$query, $tail, $updatec], "msg" => "该判断题详解已修改，不用重复修改");
                    }
                }
            }
            else if ($flag == 4) {
                //相关知识
                $rem = $request->post('rem');
                if ($rem == $query['jqrem']) {
                    return array("data" => [$query, $rem], "msg" => "两次相关知识一致，不能修改");
                } else {
                    $updatec = \Yii::$app->db->createCommand()->update('judge', ['jqrem' => $rem], "jqid={$id}")->execute();
                    if ($updatec) {
                        return array("data" => [$query, $rem, $updatec], "msg" => "该判断题相关知识修改成功");
                    } else {
                        return array("data" => [$query, $rem, $updatec], "msg" => "该判断题相关知识已修改，不用重复修改");
                    }
                }
            }
            else if ($flag == 5) {
//                状态
                $updatec = \Yii::$app->db->createCommand()->update('judge', ['jqstatus' => 1], "jqid={$id}")->execute();
                if ($updatec) {
                    return array("data" => [$query, $updatec], "msg" => "该判断题状态修改成功");
                } else {
                    return array("data" => [$query, $updatec], "msg" => "该判断题状态已修改，不用重复修改");
                }
            } else {
                return array("data" => $query, "msg" => "输入错误");
            }
        } else {
            return array("data" => $query, "msg" => "未查找到该判断题");
        }
    }
    public function actionImportexcel()
    {
        $request = \Yii::$app->request;
        $data = $request->post('data');
        $data = json_decode($data,true);
        for($i=0;$i<count($data);$i++)
        {
            $item= isset($data[$i]['item'])?$data[$i]['item']:"";
            $ans= isset($data[$i]['ans'])?$data[$i]['ans']:"";
            $tail = isset($data[$i]['tail'])?$data[$i]['tail']:"";
            $rem = isset($data[$i]['rem'])?$data[$i]['rem']:"";
            $query = (new Query())
                ->select('*')
                ->from('judge')
                ->where(['jqitem'=>$item])
                ->one();
            $id = (new Query())
                ->select("*")
                ->from('judge')
                ->where(['jqstatus'=>1])
                ->max('jqid');
            $id = $id+1;
            if($query == null)
            {
                $updatec = \Yii::$app->db->createCommand()->insert('judge',
                    array('jqid'=>$id,'jqitem'=>$item,'jqans'=>$ans,'jqtail'=>$tail,
                        'jqrem'=>$rem,'jqstatus'=>1))->execute();
            }
        }
        return array("data"=>$data,"msg"=>"导入成功");
    }
}