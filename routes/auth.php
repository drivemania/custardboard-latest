<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Illuminate\Database\Capsule\Manager as DB;

$basePath = $app->getBasePath();
function getDefaultThemeLayout() {
    $group = DB::table('groups')
        ->where('is_default', 1)
        ->first();
    
    if (!$group) {
        $group = DB::table('groups')->orderBy('created_at', 'desc')->first();
    }
    

    $themeName = $group->theme ?? 'basic';
    return array(
        "themeName" => $themeName . ".layout",
        "group" => $group
    );
}

$app->get('/login', function (Request $request, Response $response) use ($blade, $basePath) {
    if (isset($_SESSION['user_id'])) {
        return $response->withHeader('Location', $basePath . '/')->withStatus(302);
    }

    $themeArray = getDefaultThemeLayout();
    $themeLayout = $themeArray['themeName'];
    $themeUrl = $basePath . '/public/themes/' . $themeArray['group']->theme;

    $content = $blade->render('auth.login', [
        'title' => '로그인',
        'themeLayout' => $themeLayout,
        'themeUrl' => $themeUrl,
        'mainUrl' => $basePath . "/",
        'group' => $themeArray['group']
    ]);
    $response->getBody()->write($content);
    return $response;
});

$app->post('/login', function (Request $request, Response $response) use ($basePath) {
    $data = $request->getParsedBody();
    $userId = trim($data['user_id'] ?? '');
    $password = $data['password'] ?? '';

    $user = DB::table('users')->where('user_id', $userId)->first();

    if (!$user || !password_verify($password, $user->password)) {
        $_SESSION['flash_message'] = '아이디 또는 비밀번호가 일치하지 않습니다.';
        $_SESSION['flash_type'] = 'error';
        return $response->withHeader('Location', $basePath . '/login')->withStatus(302);
    }
    if ($user->is_deleted == 1) {
        $_SESSION['flash_message'] = '탈퇴한 회원입니다.';
        $_SESSION['flash_type'] = 'error';
        return $response->withHeader('Location', $basePath . '/login')->withStatus(302);
    }

    $_SESSION['user_idx'] = $user->id;
    $_SESSION['user_id']  = $user->user_id;
    $_SESSION['nickname'] = $user->nickname;
    $_SESSION['level']    = $user->level;

    return $response->withHeader('Location', $basePath . '/')->withStatus(302);
});

$app->get('/logout', function (Request $request, Response $response) use ($basePath) {
    session_destroy();
    return $response->withHeader('Location', $basePath . '/')->withStatus(302);
});

$app->get('/register', function (Request $request, Response $response) use ($blade, $basePath) {
    $themeArray = getDefaultThemeLayout();
    $themeLayout = $themeArray['themeName'];
    $themeUrl = $basePath . '/public/themes/' . $themeArray['group']->theme;

    if($themeArray['group']->is_secret > 0){
        $_SESSION['flash_message'] = '회원가입이 불가능한 커뮤니티입니다.';
        $_SESSION['flash_type'] = 'error';
        return $response->withHeader('Location', $basePath . '/login')->withStatus(302);
    }

    $content = $blade->render('auth.register', [
        'title' => '회원가입',
        'themeLayout' => $themeLayout,
        'themeUrl' => $themeUrl,
        'mainUrl' => $basePath . "/",
        'group' => $themeArray['group']
    ]);
    $response->getBody()->write($content);
    return $response;
});

$app->post('/register', function (Request $request, Response $response) use ($basePath) {
    $data = $request->getParsedBody();
    
    $userId = trim($data['user_id']);
    
    $exists = DB::table('users')->where('user_id', $userId)->exists();
    if ($exists) {
        $_SESSION['flash_message'] = '이미 사용 중인 아이디입니다.';
        $_SESSION['flash_type'] = 'error';
        return $response->withHeader('Location', $basePath . '/register')->withStatus(302);
    }

    DB::table('users')->insert([
        'user_id'    => $userId,
        'password'   => password_hash($data['password'], PASSWORD_DEFAULT),
        'nickname'   => trim($data['nickname']),
        'email'      => trim($data['email']),
        'level'      => 1,
        'created_at' => date('Y-m-d H:i:s')
    ]);

    $_SESSION['flash_message'] = '회원가입이 정상 처리되었습니다.';
    $_SESSION['flash_type'] = 'success';
    return $response->withHeader('Location', $basePath . '/')->withStatus(302);
});