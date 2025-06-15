<?php

namespace App\Helpers;

class SKUGenerator
{
  /**
   * Generate SKU for a product
   * 
   * @param string $productName
   * @param int|null $productId
   * @param string $prefix
   * @param int $length
   * @return string
   */
  public static function generateProductSKU($productName, $productId = null, $prefix = 'PRD', $length = 8)
  {
    // Clean and format product name
    $cleanName = self::cleanName($productName);

    // Take first few characters of cleaned name
    $namePrefix = substr($cleanName, 0, 3);

    // Generate random string or use product ID
    if ($productId) {
      $identifier = str_pad($productId, 4, '0', STR_PAD_LEFT);
    } else {
      $identifier = self::generateRandomString(4);
    }

    // Combine parts
    $sku = strtoupper($prefix . '-' . $namePrefix . '-' . $identifier);

    return $sku;
  }

  /**
   * Generate SKU for a product variant
   * 
   * @param string $productSKU Base product SKU
   * @param array $variantOptions Array of variant options ['color' => 'red', 'size' => 'L']
   * @param int|null $variantId
   * @return string
   */
  public static function generateVariantSKU($productSKU, $variantOptions = [], $variantId = null)
  {
    $variantParts = [];

    // Extract variant codes from options
    foreach ($variantOptions as $key => $value) {
      if (is_string($value) && !empty($value)) {
        // Take first 2 characters of each option value
        $variantParts[] = substr(self::cleanName($value), 0, 2);
      }
    }

    // Create variant suffix
    $variantSuffix = implode('', $variantParts);

    // Add variant ID or random string if no options provided
    if (empty($variantSuffix)) {
      if ($variantId) {
        $variantSuffix = 'V' . str_pad($variantId, 2, '0', STR_PAD_LEFT);
      } else {
        $variantSuffix = 'V' . self::generateRandomString(2);
      }
    }

    // Limit variant suffix length
    $variantSuffix = substr($variantSuffix, 0, 4);

    return strtoupper($productSKU . '-' . $variantSuffix);
  }

  /**
   * Generate SKU with custom format
   * 
   * @param array $parts Array of parts to combine
   * @param string $separator
   * @return string
   */
  public static function generateCustomSKU($parts, $separator = '-')
  {
    $cleanParts = array_map(function ($part) {
      return self::cleanName($part);
    }, $parts);

    return strtoupper(implode($separator, $cleanParts));
  }

  /**
   * Check if SKU already exists in database
   * 
   * @param string $sku
   * @param string $table
   * @param string $column
   * @return bool
   */
  public static function skuExists($sku, $table = 'products', $column = 'sku')
  {
    // You'll need to adapt this to your database connection
    // Example with PDO:
    /*
        global $pdo;
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM {$table} WHERE {$column} = ?");
        $stmt->execute([$sku]);
        return $stmt->fetchColumn() > 0;
        */

    // For now, return false - implement your database check here
    return false;
  }

  /**
   * Generate unique SKU that doesn't exist in database
   * 
   * @param string $baseSku
   * @param string $table
   * @param string $column
   * @param int $maxAttempts
   * @return string
   */
  public static function generateUniqueSKU($baseSku, $table = 'products', $column = 'sku', $maxAttempts = 10)
  {
    $sku = $baseSku;
    $attempt = 0;

    while (self::skuExists($sku, $table, $column) && $attempt < $maxAttempts) {
      $attempt++;
      $sku = $baseSku . '-' . $attempt;
    }

    return $sku;
  }

  /**
   * Clean name for SKU generation
   * 
   * @param string $name
   * @return string
   */
  private static function cleanName($name)
  {
    // Remove special characters and spaces
    $clean = preg_replace('/[^a-zA-Z0-9]/', '', $name);

    // If empty after cleaning, use default
    if (empty($clean)) {
      $clean = 'ITEM';
    }

    return strtoupper($clean);
  }

  /**
   * Generate random string
   * 
   * @param int $length
   * @return string
   */
  private static function generateRandomString($length = 4)
  {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $randomString = '';

    for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $randomString;
  }

  /**
   * Generate timestamp-based SKU
   * 
   * @param string $prefix
   * @return string
   */
  public static function generateTimestampSKU($prefix = 'SKU')
  {
    return strtoupper($prefix . '-' . date('YmdHis') . '-' . self::generateRandomString(2));
  }
}
