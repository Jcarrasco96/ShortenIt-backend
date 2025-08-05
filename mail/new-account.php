<?php

use SimpleApiRest\core\BaseApplication;
use SimpleApiRest\rest\Rest;

/** @var string $email */
/** @var string $password */

?>

<div class="new-account">

    <p>Estimado <?= $email ?>, bienvenido a la familia <?= Rest::$config['name'] ?></p>

    <p>Se ha creado una nueva cuenta utilizando su correo electrónico como su "nombre de usuario".</p>

    <p>Utilice la siguiente contraseña para poder iniciar sesión, le recomendamos, por su seguridad, que la cambie lo antes posible.</p>

    <p>Contraseña: <b><?= $password ?></b></p>

    <p>Si tiene alguna pregunta o tiene problemas para iniciar sesión, contáctenos <a href="mailto:<?= BaseApplication::$config['params']['supportEmail'] ?>">AQUÍ</a></p>

    <p>Copyright © <?= date('Y') ?> <?= Rest::$config['name'] ?>. All rights reserved.</p>

</div>
