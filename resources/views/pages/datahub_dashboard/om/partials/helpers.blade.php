@php
  if (!function_exists('omFormatCompact')) {
      function omFormatCompact($value) {
          $value = $value ?? 0;

          if ($value >= 1000000) {
              return number_format($value / 1000000, 2) . '<small>M</small>';
          }

          if ($value >= 1000) {
              return number_format($value / 1000, 0) . '<small>K</small>';
          }

          return number_format($value, 0);
      }
  }

  if (!function_exists('omPercentLabel')) {
      function omPercentLabel($value) {
          $value = $value ?? 0;

          if ($value > 0) {
              return '<b class="up">▲' . number_format($value, 1) . '%</b>';
          }

          if ($value < 0) {
              return '<b class="dn">▼' . number_format(abs($value), 1) . '%</b>';
          }

          return '<b class="fl">0.0%</b>';
      }
  }
@endphp
