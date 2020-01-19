<?php


namespace Wandell\Dispatch;


class Api extends Factory
{
    /**
     * 获取用户基本信息
     * @param $code
     * @return false|string
     * @throws \Exception
     */
    public function getUser($code)
    {
        $data = [
            'access_token' => $this->getAccessToken(),
            'code' => $code,
        ];
        return $this->request("user/getuserinfo" . $this->make_url_query($data));
    }

    /**
     * 获取用户的详细信息
     * @param $userid
     * @return false|string
     * @throws \Exception
     */
    public function getUserInfo($userid)
    {
        $data = [
            'access_token' => $this->getAccessToken(),
            'userid' => $userid,
        ];
        return $this->request("user/get" . $this->make_url_query($data));
    }

    /**
     * 获取应用管理员的身份信息
     * @param $code
     * @return false|string
     * @throws \Exception
     */
    public function ssoGetUserInfo($code)
    {
        $data = [
            'access_token' => $this->getSsoToken(),
            'code' => $code,
        ];
        return $this->request("sso/getuserinfo" . $this->make_url_query($data));
    }

    /**
     * 获取部门列表
     * @param $id
     * @param bool $fetch_child
     * @return false|string
     * @throws \Exception
     */
    public function departmentList($id = 0, $fetch_child = true)
    {
        $data = [
            'access_token' => $this->getAccessToken(),
            'fetch_child' => $fetch_child,
        ];
        if ($id) {
            $data['id'] = $id;
        }

        return $this->request("department/list" . $this->make_url_query($data));
    }

    /**
     * 获取部门用户
     * @param $department_id
     * @param null $offset
     * @param null $size
     * @param null $order
     * @return false|string
     * @throws \Exception
     */
    public function userSimplelist($department_id, $offset = null, $size = null, $order = null)
    {
        $data = [
            'access_token' => $this->getAccessToken(),
            'department_id' => $department_id,
        ];
        if ($offset && $size) {
            $data['offset'] = $offset;
            $data['size'] = $size;
        }
        if ($order) {
            $data['order'] = $order;
        }

        return $this->request("user/simplelist" . $this->make_url_query($data));
    }

    /**
     * 发送工作通知消息
     * @param $userid_list
     * @param $memssage
     * @return false|string
     * @throws \Exception
     */
    public function sendMess($userid_list, $message)
    {
        $get_data = [
            'access_token' => $this->getAccessToken(),
        ];
        $msg_arr = [
            "msgtype"=>"text",
            "text"=>[
                "content"=>$message
            ]
        ];
        $post_data = [
            'agent_id' => $this->getConfig()['agent_id'],
            'userid_list' => $userid_list,
            'msg' => $msg_arr
        ];
        
        return $this->request("/topapi/message/corpconversation/asyncsend_v2" . $this->make_url_query($get_data), $post_data, "post");
    }
}