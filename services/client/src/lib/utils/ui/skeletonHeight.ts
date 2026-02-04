const SKELETON_HEIGHTS = [
  280, 340, 220, 380, 260, 320, 400, 240, 360, 300, 250, 390,
];

export function getSkeletonHeight(
  index: number,
  baseHeight: number = 0,
): number {
  return baseHeight + SKELETON_HEIGHTS[index % SKELETON_HEIGHTS.length];
}
