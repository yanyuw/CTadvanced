<?php

namespace backend\module\bank\controllers;

use yii\web\Controller;
use yii\common\models\Fillq;
use yii\db\Query;

/**
 * Default controller for the `bank` module
 * 填空题题：固定模式，一个题干，一个答案
 */
class FillqController extends Controller
{
    public function actionIndex()
    {
        return "bank-fillq"; // TODO: Change the autogenerated stub
    }
    /*
     * 填空题
     */

    /**
     * fillq model
     * @property integer $fqid
     * @property string $fqitem
     * @property string $fqans
     * @property string $fqtail
     * @property string $fqrem
     * @property string $fqstatus
     */
    /*
     * 查找全部的填空题
     * 标志：flag
     * 1:全部的填空题
     * 2：有效的填空题
     * 3：模糊查找某题
     * 4：无效的填空题
     */
    public function actionQueryfill()
    {
        $request = \Yii::$app->request;
        $flag = $request->post('flag');
        if ($flag == 1) {
            $query = (new Query())
                ->select("*")
                ->from('fillq')
                ->all();
            return array("data" => $query, "msg" => "全部的填空题");
        } else if ($flag == 2) {
            $query = (new Query())
                ->select("*")
                ->from('fillq')
                ->where(['fqstatus' => 1])
                ->all();
            return array("data" => $query, "msg" => "有效的填空题");
        } else if ($flag == 3) {
            $name = $request->post('name');
            $query = (new Query())
                ->select("*")
                ->from('fillq')
                ->where(['or',
                    ['like', 'fqitem', $name],
                    ['like', 'fqans', $name],
                    ['like', 'fqtail', $name],
                    ['like', 'fqrem', $name],
                ])
                ->all();
            return array("data" => $query, "msg" => $name . "填空题");
        } else if ($flag == 4) {
            $query = (new Query())
                ->select("*")
                ->from('fillq')
                ->where(['fqstatus' => 0])
                ->all();
            return array("data" => $query, "msg" => "无效的填空题");
        } else {
            return array("data" => $flag, "msg" => "输入错误");
        }
    }

    /*
     * 增加填空题：参数(题干、答案、详解、相关知识)
     * 选择：设置为四个选项，固定的模式
     */
    public function actionAddfill()
    {
        $id = (new Query())
            ->select("*")
            ->from('fillq')
            ->max('fqid');
        $id = $id + 1;
        $request = \Yii::$app->request;
        $item = $request->post('qitem');
        $ans = $request->post('ans');
        $tail = $request->post('tail');
        $rem = $request->post('rem');
        $query = (new Query())
            ->select('*')
            ->from('fillq')
            ->where(['fqitem' => $item])
            ->one();
        if ($query) {
            return array("data" => $query, "msg" => "该题已在题库中，请勿重复添加");
        } else {
            $updatec = \Yii::$app->db->createCommand()->insert('fillq',
                array('fqid' => $id, 'fqitem' => $item, 'fqans' => $ans, 'fqtail' => $tail,
                    'fqrem' => $rem, 'fqstatus' => 1))->execute();
            if ($updatec) {
                return array("data" => $updatec, "msg" => "插入填空题成功");
            } else {
                return array("data" => $updatec, "msg" => "插入失败，该题已插入");
            }
        }
    }

    /*
     * 删除填空题：一个函数实现
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
            ->from('fillq')
            ->where(['fqid' => $id])
            ->one();
        if ($query) {
            $flag = $requset->post('flag');
            if ($flag == 1) {
                //暂时删除
                $updatec = \Yii::$app->db->createCommand()->update('fillq', ['fqstatus' => 0], "fqid={$id}")->execute();
                if ($updatec) {
                    return array("data" => [$query, $updatec], "msg" => "该填空题删除成功");
                } else {
                    return array("data" => [$query, $updatec], "msg" => "该填空题已删除，不用重复删除");
                }
            } else if ($flag == 2) {
                //永久删除
                $updatec = \Yii::$app->db->createCommand()->delete('fillq', ['fqid' => $id])->execute();
                if ($updatec) {
                    return array("data" => [$query, $updatec], "msg" => "该填空题永久删除成功");
                } else {
                    return array("data" => [$query, $updatec], "msg" => "该填空题已永久删除，不用重复删除");
                }
            } else {
                return array("data" => [$query, $flag], "msg" => "输入错误");
            }

        } else {
            return array("data" => $query, "msg" => "没有找到该填空题");
        }
    }

    /*
     * 修改填空题相关内容：一个函数实现
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
            ->from('fillq')
            ->where(['fqid' => $id])
            ->one();
        if ($query) {
            $flag = $request->post('flag');
            if ($flag == 1) {
                //题干
                $item = $request->post('item');
                if ($item == $query['fqitem']) {
                    return array("data" => [$query, $item], "msg" => "两次题干一致，不能修改");
                } else {
                    $updatec = \Yii::$app->db->createCommand()->update('fillq', ['fqitem' => $item], "fqid={$id}")->execute();
                    if ($updatec) {
                        return array("data" => [$query, $item, $updatec], "msg" => "该填空题题干修改成功");
                    } else {
                        return array("data" => [$query, $item, $updatec], "msg" => "该填空题题干已修改，不用重复修改");
                    }
                }
            }
            else if ($flag == 2) {
                //正确答案
                $ans = $request->post('ans');
                if ($ans == $query['fqans']) {
                    return array("data" => [$query, $ans], "msg" => "两次答案一致，不能修改");
                } else {
                    $updatec = \Yii::$app->db->createCommand()->update('fillq', ['fqans' => $ans], "fqid={$id}")->execute();
                    if ($updatec) {
                        return array("data" => [$query, $ans, $updatec], "msg" => "该填空题答案修改成功");
                    } else {
                        return array("data" => [$query, $ans, $updatec], "msg" => "该填空题答案已修改，不用重复修改");
                    }
                }
            }
            else if ($flag == 3) {
                //详解
                $tail = $request->post('tail');
                if ($tail == $query['fqtail']) {
                    return array("data" => [$query, $tail], "msg" => "两次详解一致，不能修改");
                } else {
                    $updatec = \Yii::$app->db->createCommand()->update('fillq', ['fqtail' => $tail], "fqid={$id}")->execute();
                    if ($updatec) {
                        return array("data" => [$query, $tail, $updatec], "msg" => "该填空题详解修改成功");
                    } else {
                        return array("data" => [$query, $tail, $updatec], "msg" => "该填空题详解已修改，不用重复修改");
                    }
                }
            }
            else if ($flag == 4) {
                //相关知识
                $rem = $request->post('rem');
                if ($rem == $query['fqrem']) {
                    return array("data" => [$query, $rem], "msg" => "两次相关知识一致，不能修改");
                } else {
                    $updatec = \Yii::$app->db->createCommand()->update('fillq', ['fqrem' => $rem], "fqid={$id}")->execute();
                    if ($updatec) {
                        return array("data" => [$query, $rem, $updatec], "msg" => "该填空题相关知识修改成功");
                    } else {
                        return array("data" => [$query, $rem, $updatec], "msg" => "该填空题相关知识已修改，不用重复修改");
                    }
                }
            }
            else if ($flag == 5) {
//                状态
                $updatec = \Yii::$app->db->createCommand()->update('fillq', ['fqstatus' => 1], "fqid={$id}")->execute();
                if ($updatec) {
                    return array("data" => [$query, $updatec], "msg" => "该填空题状态修改成功");
                } else {
                    return array("data" => [$query, $updatec], "msg" => "该填空题状态已修改，不用重复修改");
                }
            } else {
                return array("data" => $query, "msg" => "输入错误");
            }
        } else {
            return array("data" => $query, "msg" => "未查找到该填空题");
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
                ->from('fillq')
                ->where(['fqitem'=>$item])
                ->one();
            $id = (new Query())
                ->select("*")
                ->from('fillq')
                ->where(['fqstatus'=>1])
                ->max('fqid');
            $id = $id+1;
            if($query == null)
            {
                $updatec = \Yii::$app->db->createCommand()->insert('fillq',
                    array('fqid'=>$id,'fqitem'=>$item,'fqans'=>$ans,'fqtail'=>$tail,
                        'fqrem'=>$rem,'fqstatus'=>1))->execute();
            }
        }
        return array("data"=>$data,"msg"=>"导入成功");
    }
}