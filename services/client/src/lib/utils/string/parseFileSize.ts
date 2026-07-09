enum SizeUnitType {
  k = 'KB',
  M = 'MB',
}

type SizeUnitValue = keyof typeof SizeUnitType;
type SizeUnit = (typeof SizeUnitType)[SizeUnitValue];

export const sizeMatchingRegex = new RegExp(
  `(\\d+)(${Object.keys(SizeUnitType).join('|')})`,
);

const formatSizeUnit = (unit: SizeUnitValue): SizeUnit => {
  return SizeUnitType[unit];
};

export const parseFileSize = (
  fileSize: string,
): {
  size: string;
  unit: SizeUnit;
  unitValue: SizeUnitValue;
} => {
  const match = fileSize.match(/(\d+)?(\w+)/);

  if (!match) {
    throw new Error(`Invalid file size format: ${fileSize}`);
  }

  const [, numberValue, SizeUnit] = match;

  return {
    size: numberValue,
    unit: formatSizeUnit(SizeUnit as SizeUnitValue),
    unitValue: SizeUnit as SizeUnitValue,
  };
};

export const formatFileSize = (fileSize?: string | null): string | null => {
  if (!fileSize) return null;

  try {
    const { size, unit } = parseFileSize(fileSize);

    if (!size || !unit || Number.isNaN(Number(size))) {
      return null;
    }

    return `${size} ${unit}`;
  } catch {
    return null;
  }
};
