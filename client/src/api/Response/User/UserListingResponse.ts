import type { ListingMetadata } from '@slink/api/Response';
import type { SingleUserResponse } from '@slink/api/Response/User/SingleUserResponse';

export type UserListingItem = SingleUserResponse;

export type UserListingResponse = {
  meta: ListingMetadata;
  data: UserListingItem[];
};
