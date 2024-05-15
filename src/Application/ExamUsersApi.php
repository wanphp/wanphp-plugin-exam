<?php

namespace Wanphp\Plugins\Exam\Application;

use Psr\Http\Message\ResponseInterface as Response;
use Wanphp\Libray\Slim\WpUserInterface;
use Wanphp\Plugins\Exam\Domain\ExamScoreInterface;

class ExamUsersApi extends Api
{
  private ExamScoreInterface $examScore;
  private WpUserInterface $user;

  public function __construct(ExamScoreInterface $examScore, WpUserInterface $user)
  {
    $this->examScore = $examScore;
    $this->user = $user;
  }

  /**
   * @inheritDoc
   */
  protected function action(): Response
  {
    $where = ['examId' => $this->resolveArg('id')];
    $params = $this->request->getQueryParams();
    if (isset($params['q']) && $params['q'] != '') {
      $keyword = trim($params['q']);
      $where['OR'] = [
        'name[~]' => $keyword,
        'tel[~]' => $keyword
      ];
    }
    $where['LIMIT'] = [$params['start'], $params['length']];
    $where['ORDER'] = ['id' => 'DESC'];

    $users = $this->examScore->select('id,uid,name,tel,score,startTime,endTime', $where);


    if ($users) {
      $uid = array_column($users, 'uid');
      $weUser = $this->user->getUsers($uid);

      $index = array_column($weUser, 'id');
      foreach ($users as &$u) {
        $user = $weUser[array_search($u['uid'], $index)];
        $u['headimgurl'] = $user['headimgurl'];
        $u['nickname'] = $user['nickname'];
        // 答题用时
        $u['examTime'] = $u['endTime'] - $u['startTime'];
        $upUser = [];
        if (empty($u['name']) && !empty($user['name'])) {
          $upUser['name'] = $u['name'] = $user['name'];
        }
        if (empty($u['tel']) && !empty($user['tel'])) {
          $upUser['tel'] = $u['tel'] = $user['tel'];
        }

        if ($u['name']) $u['name'] = mb_substr($u['name'], 0, 1, 'utf-8') . '*' . mb_substr($u['name'], -1, 1, 'utf-8');
        if ($u['tel']) $u['tel'] = substr_replace($u['tel'], '****', 3, 4);
        if ($upUser) $this->examScore->update($upUser, ['id' => $u['id']]);
      }
      return $this->respondWithData($users);
    } else return $this->respondWithData();
  }
}
