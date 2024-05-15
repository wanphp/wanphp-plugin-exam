<?php
declare(strict_types=1);

use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use Psr\Http\Server\MiddlewareInterface as Middleware;

return function (App $app, Middleware $PermissionMiddleware, Middleware $OAuthServerMiddleware) {
  $app->group('/api', function (Group $group) {
    //知识竞赛
    $group->get('/exam[/{id:[0-9]+}]', \Wanphp\Plugins\Exam\Application\ExamItemApi::class);
    //知识竞赛题目
    $group->get('/exam/question/{id:[0-9]+}', \Wanphp\Plugins\Exam\Application\randQuestionsApi::class);
    //知识竞赛成绩
    $group->map(['GET', 'POST'], '/exam/score[/{id:[0-9]+}]', \Wanphp\Plugins\Exam\Application\UserScoreApi::class);
    $group->get('/currentServerTime', \Wanphp\Plugins\Exam\Application\UserScoreApi::class . ':currentServerTime');
    //参与用户
    $group->get('/exam/users/{id:[0-9]+}', \Wanphp\Plugins\Exam\Application\ExamUsersApi::class);
  })->addMiddleware($OAuthServerMiddleware);

  $app->group('/admin', function (Group $group) {
    //知识竞赛管理
    $group->map(['GET', 'PUT', 'POST', 'DELETE'], '/exam[/{id:[0-9]+}]', \Wanphp\Plugins\Exam\Application\Manage\ExamApi::class);
    $group->get('/exam/content[/{id:[0-9]+}]', \Wanphp\Plugins\Exam\Application\Manage\ExamContentApi::class);
    //知识竞赛题目管理
    $group->map(['GET', 'PUT', 'POST', 'DELETE'], '/exam/question[/{id:[0-9]+}]', \Wanphp\Plugins\Exam\Application\Manage\ExamQuestionsApi::class);
    //下载题库表格模板
    $group->get('/download/question/template', \Wanphp\Plugins\Exam\Application\Manage\OutputQuestionTplApi::class);
    //导入题库
    $group->post('/import/question/{id:[0-9]+}', \Wanphp\Plugins\Exam\Application\Manage\ImportQuestionApi::class);
    //知识竞赛记录
    $group->map(['GET', 'POST'], '/exam/record/{id:[0-9]+}', \Wanphp\Plugins\Exam\Application\Manage\ExamRecordApi::class);
    //下载知识竞赛记录
    $group->get('/download/exam/record/{id:[0-9]+}', \Wanphp\Plugins\Exam\Application\Manage\OutputExamRecordApi::class);
  })->addMiddleware($PermissionMiddleware);
};


