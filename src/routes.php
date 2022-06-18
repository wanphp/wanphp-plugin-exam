<?php
declare(strict_types=1);

use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use Psr\Http\Server\MiddlewareInterface as Middleware;

return function (App $app, Middleware $PermissionMiddleware, Middleware $OAuthServerMiddleware) {
  $app->group('/api', function (Group $group) {
    //考试科目
    $group->get('/exam/{id:[0-9]+}', \Wanphp\Plugins\Exam\Application\ExamItemApi::class);
    //考试题目
    $group->get('/exam/question/{id:[0-9]+}', \Wanphp\Plugins\Exam\Application\randQuestionsApi::class);
    //考试成绩
    $group->map(['GET', 'POST'], '/exam/score[/{id:[0-9]+}]', \Wanphp\Plugins\Exam\Application\UserScoreApi::class);
  })->addMiddleware($OAuthServerMiddleware);

  $app->group('/admin', function (Group $group) {
    //考试科目管理
    $group->map(['PUT', 'POST', 'DELETE'], '/exam[/{id:[0-9]+}]', \Wanphp\Plugins\Exam\Application\Manager\ExamApi::class);
    $group->get('/exam/content[/{id:[0-9]+}]', \Wanphp\Plugins\Exam\Application\Manager\ExamContent::class);
    //考试题目管理
    $group->map(['GET', 'PUT', 'POST', 'DELETE'], '/exam/question[/{id:[0-9]+}]', \Wanphp\Plugins\Exam\Application\Manager\ExamQuestionsApi::class);
    //考试记录
    $group->get('/exam/record/{id:[0-9]+}', \Wanphp\Plugins\Exam\Application\Manager\ExamRecordApi::class);
  })->addMiddleware($PermissionMiddleware);
};


