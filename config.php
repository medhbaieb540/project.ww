<?php

// Prevent class redeclaration
if (!class_exists('config')) {

class config

{
  private static $pdo = null;
  // OpenAI API Configuration: can be set here, via constant, or environment variable
  // TEMPORARY: Paste your API key here for testing (then remove it and use env var)
  private static $openaiApiKey = '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';

  // Return OpenAI API key for server-side usage if available.
  // Prioritizes an explicitly-set `$openaiApiKey`, then constant, then environment.
  // Returns null when not set.
  public static function getOpenAiApiKey(): ?string
  {
    if (!empty(self::$openaiApiKey)) {
      return self::$openaiApiKey;
    }
    if (defined('OPENAI_API_KEY') && OPENAI_API_KEY) {
      return OPENAI_API_KEY;
    }
    $k = getenv('OPENAI_API_KEY');
    if ($k !== false && $k !== '') return $k;
    return null;
  }

  public static function getConnexion()

  {
    if (!isset(self::$pdo)) {
      try {
        self::$pdo = new PDO(
          'mysql:host=localhost;dbname=gamebridge;charset=utf8',
          'root',
          '',
          [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
          ]
        );
      } catch (Exception $e) {
        die('Error: ' . $e->getMessage());
      }
    }
    return self::$pdo;
  }

}

} // End class_exists check