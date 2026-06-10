<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service\Upload;

enum UploadPhase: int {
  case Prepare = 500;
  case Transform = 400;
  case Describe = 300;
  case Resolve = 200;
  case Persist = 100;
}
