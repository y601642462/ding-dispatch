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
     * 获取部门用户详情
     * @param $department_id
     * @param null $offset
     * @param null $size
     * @param null $order
     * @return false|string
     * @throws \Exception
     */
    public function getDetailedUsers($department_id,$offset = null, $size = null, $order = null)
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

        return $this->request("user/listbypage" . $this->make_url_query($data));
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
            "msgtype" => "text",
            "text" => [
                "content" => $message
            ]
        ];
        $post_data = [
            'agent_id' => $this->getConfig()['agent_id'],
            'userid_list' => $userid_list,
            'msg' => $msg_arr
        ];

        return $this->request("/topapi/message/corpconversation/asyncsend_v2" . $this->make_url_query($get_data), $post_data, "post");
    }

    /**
     * 获取签名相关信息
     *
     * @param string $url
     *
     * @return mixed
     */
    public function getSignature($url)
    {
        $nonceStr = $this->getSuiteTicket();
        $timeStamp = time();
        $plain = 'jsapi_ticket=' . $this->getTicket() . '&noncestr=' . $nonceStr . '&timestamp=' . $timeStamp . '&url=' . $url;
        $signature = sha1($plain);
        return [
            'agentId' => $this->getConfig()['agent_id'],
            'corpId' => $this->getConfig()['auth_corpid'],
            'timeStamp' => $timeStamp,
            'nonceStr' => $nonceStr,
            'signature' => $signature,
            'url' => $url
        ];
    }

    /**
     * 获取用户发出的日志列表
     * @param $start_time@开始时间戳
     * @param $end_time@结束时间戳
     * @param $cursor@游标
     * @param string $template_name@模板名称
     * @param string $user_id@用户
     * @return false|string
     * @throws \Exception
     */
    public function reportList($start_time, $end_time, $cursor, $template_name = '', $user_id = '')
    {
        $data = [
            'access_token' => $this->getAccessToken(),
            'start_time' => $start_time,
            'end_time' => $end_time,
            'cursor' => $cursor,
            'size' => 20,
        ];
        if ($template_name) {
            $data['template_name'] = $template_name;
        }
        if ($user_id) {
            $data['userid'] = $user_id;
        }

        return $this->request("topapi/report/list" . $this->make_url_query($data));
    }

    /**
     * 获取子部门ID
     * @param $department_id
     * @return false|string
     * @throws \Exception
     */
    public function departmentListIds($department_id)
    {
        $data = [
            'access_token' => $this->getAccessToken(),
            'id' => $department_id,
        ];

        return $this->request("department/list_ids" . $this->make_url_query($data));
    }

    /**
     * 获取部门详情
     * @param $department_id
     * @return false|string
     * @throws \Exception
     */
    public function departmentInfo($department_id)
    {
        $data = [
            'access_token' => $this->getAccessToken(),
            'id' => $department_id,
        ];

        return $this->request("department/get" . $this->make_url_query($data));
    }

    /**
     * 获取部门中所有用户id
     * @param $department_id
     * @return false|string
     * @throws \Exception
     */
    public function getDepartmentUserids($department_id)
    {
        $data = [
            'access_token' => $this->getAccessToken(),
            'deptId' => $department_id,
        ];

        return $this->request("user/getDeptMember" . $this->make_url_query($data));
    }

    /**
     * 获取指定用户日志模板
     * @param $userid
     * @param $template_name
     * @return false|string
     * @throws \Exception
     */
    public function getTemplate($userid,$template_name)
    {
        $data = [
            'access_token' => $this->getAccessToken(),
            'userid' => $userid,
            'template_name' => $template_name,
        ];

        return $this->request("topapi/report/template/getbyname" . $this->make_url_query($data));
    }
}