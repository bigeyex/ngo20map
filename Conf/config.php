<?php
return array(
	//'配置项'=>'配置值'
    'APP_DEBUG' => true,
    'TOKEN_ON' => false,
    'URL_MODEL' => 2,
    'APP_PLUGIN_ON' => true,
    'ITEM_PER_PAGE' => 20,
    'SESSION_AUTO_START' => true,
    'APP_AUTOLOAD_PATH' => 'Think.Util.,@.Util.,@.Util.Tencent',
    'LANG_SWITCH_ON' => true,
    'MAP_APICONNECTSTR' => 'http://api.map.baidu.com/api?key=46ce9d0614bf7aefe0ba562f8cf87194&v=1.2&services=true',
    'QQ_APPKEY' => '',
    'QQ_APPSECRET' => '',
    'WEIBO_APPKEY' => '',
    'WEIBO_APPSECRET' => '',
    'DB_TYPE' => 'mysql',
    'DB_HOST' => 'localhost',
    'DB_NAME' => 'ngo20map',
    'DB_USER' => 'root',
    'DB_PWD' => '',
    'DB_PORT' => '3306',
    'DB_PREFIX' => '',
    'APP_CONFIG_LIST' => array('taglibs','routes','htmls','modules','actions','tags','auth','profanity','paragraphs'),
    'LANGUAGES'=> array('zh-cn'=>'中文', 'en-us'=>'English'),
    'ORG_FIELDS' => array('健康扶助','妇女儿童','教育助学','环境保护','社区发展','灾害管理',
            '劳工福利','社会企业','HIV&AIDS','文化保护','性权利保护','政策倡导','信息网络','支持型NGO'),
    'VOLUNTEER_SKILLS' => array('外语', 'IT基础设施维护', '网络编程', '美工', '营销', '法律'),
    'USER_TYPE' => array(
    		'ngo' => '公益组织',
    		'csr' => '企业',
    		'fund' => '基金会',
    		'ind' => '公益人'
    ),
    'EVENT_TYPES' => array(
    		'ngo' => '公益项目',
    		'csr' => '企业项目',
    		'fund' => '基金会项目',
    		'ind' => '公益报道',
    		'case' => '对接案例'
    ),
    //需求和资源的类型
    'EVENT_TYPE' => array(
    					'requirement' => array('需要合作方','媒体需求','物资需求','资金需求','志愿者需求'),
                        'resource' => array('捐赠物资','捐赠款项','志愿者'),
        ),
    'HOMEPAGE_TAG' => array('留守儿童','义工','农民工','捐书','助学','乡村教育','电脑教室','希望小学',
                    '爱心图书室','爱心书包','夏令营','结对','残疾','艾滋','心理健康','环保','植树','低碳','志愿者','孤儿','社区服务','老人','自闭症','妇女','企业社会责任','智障','地震','白血病','捐衣','聋哑','白内障'),
    'EVENTVIEW_DETAILLIST_NUM' => 5,
    'HOMEPAGE_RECENTREVIEW_NUM' => 5,
    'HOMEPAGE_RECENTEVENT_NUM' => 5,
    'HOMEPAGE_RSSNEWS_COUNT' => 5,
    'HOMEPAGE_RSSNEWS_URL' => 'http://www.ngo20.org/?feed=rss2',
    'SEARCH_DETAILLIST_NUM' => 15,

    //搜索结果页相关参数
    'SEARCHRESULT_LISTROWS' => 20,
    'SEARCHRESULT_DESCRIPTION_NUM' => 200,
    'SEARCHDETAIL_DESCRIPTION_NUM' => 500,
    'SEARCH_USERDETAIL_EVENT_NUM' => 5,
    'SEARCH_EVENTDETAIL_REVTEW_NUM' => 5,
    'SEARCH_EVENTDETAIL_MEDIA_NUM' => 3,

    'USERHOME_EVENT_COUNT'=> 20,
    'USERHOME_EVENT_PREVIEW'=> 3,
    'USERHOME_REVIEW_COUNT'=> 5,

    //后台管理页面相关参数
    'ADMINEVENTS_LISTROWS' => 20,

    'ADMIN_LIST_COUNT'=>20,

    //事件查看相关media参数
    'EVENTVIEW_IMG_NUM'=>6,
    'EVENTVIEW_VEDIO_NUM'=>5,
    'EVENTVIEW_REVIEW_NUM'=>5,  //事件查看时每页显示的评论数

    'TALK_LIST_NUM'=>7,
    'REVIEW_PER_PAGE'=>20,
    'SEARCH_RESULT_PER_PAGE'=>20,

);
?>