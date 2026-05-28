import { localize } from '@slink/lib/utils/i18n';

type Labels = { title: string; name: string; description: string };

export function getLicenseLabels(id: string): Labels {
  switch (id) {
    case 'all-rights-reserved':
      return {
        title: localize('All Rights Reserved'),
        name: localize('All Rights Reserved'),
        description: localize(
          'All rights are reserved by the copyright holder. You may not use, copy, or distribute this work without explicit permission.',
        ),
      };
    case 'public-domain':
      return {
        title: localize('Public Domain'),
        name: localize('Public Domain Work'),
        description: localize(
          'This work is free of known copyright restrictions and can be used without permission.',
        ),
      };
    case 'cc0':
      return {
        title: localize('CC0'),
        name: localize('Public Domain Dedication (CC0)'),
        description: localize(
          'The creator has waived all rights and dedicated this work to the public domain. You can use it for any purpose without restriction.',
        ),
      };
    case 'cc-by':
      return {
        title: localize('CC BY'),
        name: localize('Attribution'),
        description: localize(
          'You may distribute, remix, adapt, and build upon this work, even commercially, as long as you credit the creator.',
        ),
      };
    case 'cc-by-sa':
      return {
        title: localize('CC BY-SA'),
        name: localize('Attribution-ShareAlike'),
        description: localize(
          'You may remix, adapt, and build upon this work even commercially, as long as you credit the creator and license new creations under identical terms.',
        ),
      };
    case 'cc-by-nc':
      return {
        title: localize('CC BY-NC'),
        name: localize('Attribution-NonCommercial'),
        description: localize(
          'You may remix, adapt, and build upon this work non-commercially, as long as you credit the creator.',
        ),
      };
    case 'cc-by-nc-sa':
      return {
        title: localize('CC BY-NC-SA'),
        name: localize('Attribution-NonCommercial-ShareAlike'),
        description: localize(
          'You may remix, adapt, and build upon this work non-commercially, as long as you credit the creator and license new creations under identical terms.',
        ),
      };
    case 'cc-by-nd':
      return {
        title: localize('CC BY-ND'),
        name: localize('Attribution-NoDerivs'),
        description: localize(
          'You may copy and distribute this work, but not adapt it, as long as you credit the creator.',
        ),
      };
    case 'cc-by-nc-nd':
      return {
        title: localize('CC BY-NC-ND'),
        name: localize('Attribution-NonCommercial-NoDerivs'),
        description: localize(
          'You may copy and distribute this work non-commercially, but not adapt it, as long as you credit the creator.',
        ),
      };
    default:
      return { title: id, name: id, description: '' };
  }
}
