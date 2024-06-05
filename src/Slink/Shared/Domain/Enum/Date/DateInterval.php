<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\Enum\Date;

enum DateInterval: string{
  case CURRENT_WEEK = 'Current Week';
  case LAST_WEEK = 'Last Week';
  case LAST_7_DAYS = 'Last 7 Days';
  case CURRENT_MONTH = 'Current Month';
  case LAST_MONTH = 'Last Month';
  case LAST_30_DAYS = 'Last 30 Days';
  case CURRENT_YEAR = 'Current Year';
  case ALL_TIME = 'All Time';
}
