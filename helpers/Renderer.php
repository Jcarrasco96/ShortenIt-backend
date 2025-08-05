<?php

namespace ShortenIt\helpers;

use SimpleApiRest\core\BaseApplication;
use SimpleApiRest\exceptions\ServerErrorHttpException;

class Renderer
{

    /**
     * @throws ServerErrorHttpException
     */
    private static function renderPartial(string $view, array $params = []): string
    {
        $viewPath = "mail/$view.php";

        if (!file_exists($viewPath)) {
            throw new ServerErrorHttpException(BaseApplication::t('The view "{view}" does not exist.', [$viewPath]));
        }

        extract($params);

        ob_start();
        require $viewPath;
        return ob_get_clean() ?: throw new ServerErrorHttpException(BaseApplication::t('Internal error on the server. Contact the administrator.'));
    }

    /**
     * @throws ServerErrorHttpException
     */
    public static function render(string $view, array $params = []): string
    {
        $content = self::renderPartial($view, $params);

        return self::renderPartial('_main', ['content' => $content]);
    }

}