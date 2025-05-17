<?php
function hashPassword($password) {
    $salt = "quoicoube";
    $salt_password = $password . $salt;
    $hash_password = hash("sha256", $salt_password);
    return $hash_password;
}
