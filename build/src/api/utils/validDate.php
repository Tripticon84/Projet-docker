<?php
function isValidDate(string $date): bool {
    // Vérifie si la date est au format YYYY-MM-DD
    $dateTime = DateTime::createFromFormat('Y-m-d', $date);
    return $dateTime && $dateTime->format('Y-m-d') === $date;
}