export type License = {
  id: string;
  title: string;
  name: string;
  description: string;
  url: string | null;
  isCreativeCommons: boolean;
};

export type LicenseId =
  | 'all-rights-reserved'
  | 'public-domain'
  | 'cc0'
  | 'cc-by'
  | 'cc-by-sa'
  | 'cc-by-nc'
  | 'cc-by-nc-sa'
  | 'cc-by-nd'
  | 'cc-by-nc-nd';
