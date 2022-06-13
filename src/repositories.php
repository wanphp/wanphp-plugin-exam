<?php
declare(strict_types=1);

use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
  $containerBuilder->addDefinitions([
    \Wanphp\Plugins\Exam\Domain\ExamQuestionsInterface::class => \DI\autowire(\Wanphp\Plugins\Exam\Repositories\ExamQuestionRepository::class),
    \Wanphp\Plugins\Exam\Domain\ExamScoreInterface::class => \DI\autowire(\Wanphp\Plugins\Exam\Repositories\ExamScoreRepository::class),
    \Wanphp\Plugins\Exam\Domain\ExamInterface::class => \DI\autowire(\Wanphp\Plugins\Exam\Repositories\ExamRepository::class)
  ]);
};
