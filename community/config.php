<?php
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=gamebridge;charset=utf8",
        "root",
        ""
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

/* ✅ BAD WORD FILTER (ONLY THESE 3 WORDS) */
function filterBadWords($text) {
    $badWords = ['fuck', 'shit', 'stupid'];

    foreach ($badWords as $word) {
        $pattern = '/\b' . preg_quote($word, '/') . '\b/i';
        $text = preg_replace($pattern, str_repeat('*', strlen($word)), $text);
    }

    return $text;
}
?>
