export enum SortOrder {
  Asc = 'asc',
  Desc = 'desc',
}

export function toggleSortOrder(order: SortOrder): SortOrder {
  return order === SortOrder.Asc ? SortOrder.Desc : SortOrder.Asc;
}
