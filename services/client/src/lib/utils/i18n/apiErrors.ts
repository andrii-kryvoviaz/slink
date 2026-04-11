import { runtimeTranslator } from './RuntimeTranslator.svelte';
import { localize } from './localize';

type ApiError = {
  match: string | RegExp;
  translate: (...args: any[]) => string;
};

function errors(): ApiError[] {
  return [
    {
      match: /* @wc-ignore */ 'Passwords do not match.',
      translate: () => localize('Passwords do not match.'),
    },
    {
      match: /* @wc-ignore */ 'Username cannot be the same as email.',
      translate: () => localize('Username cannot be the same as email.'),
    },
    {
      match: /* @wc-ignore */ 'Password must contain at least one number.',
      translate: () => localize('Password must contain at least one number.'),
    },
    {
      match:
        /* @wc-ignore */ 'Password must contain at least one lowercase letter.',
      translate: () =>
        localize('Password must contain at least one lowercase letter.'),
    },
    {
      match:
        /* @wc-ignore */ 'Password must contain at least one uppercase letter.',
      translate: () =>
        localize('Password must contain at least one uppercase letter.'),
    },
    {
      match:
        /* @wc-ignore */ 'Password must contain at least one special character.',
      translate: () =>
        localize('Password must contain at least one special character.'),
    },
    {
      match: /* @wc-ignore */ 'Min 6 characters password',
      translate: () => localize('Min 6 characters password'),
    },
    {
      match:
        /* @wc-ignore */ 'Username can only contain lowercase letters, numbers, underscores, hyphens, and periods.',
      translate: () =>
        localize(
          'Username can only contain lowercase letters, numbers, underscores, hyphens, and periods.',
        ),
    },
    {
      match:
        /* @wc-ignore */ 'Username cannot contain consecutive special characters.',
      translate: () =>
        localize('Username cannot contain consecutive special characters.'),
    },
    {
      match:
        /* @wc-ignore */ 'Invalid credentials. Please check your username/email and password and try again.',
      translate: () =>
        localize(
          'Invalid credentials. Please check your username/email and password and try again.',
        ),
    },
    {
      match: /* @wc-ignore */ 'You must be logged in to change your password',
      translate: () =>
        localize('You must be logged in to change your password'),
    },
    {
      match: /* @wc-ignore */ 'Invalid refresh token',
      translate: () => localize('Invalid refresh token'),
    },
    {
      match: /* @wc-ignore */ 'User is restricted',
      translate: () => localize('User is restricted'),
    },
    {
      match:
        /* @wc-ignore */ 'SSO provider is currently unavailable. Please try again later.',
      translate: () =>
        localize(
          'SSO provider is currently unavailable. Please try again later.',
        ),
    },
    {
      match: /* @wc-ignore */ 'Invalid email address.',
      translate: () => localize('Invalid email address.'),
    },
    {
      match: /* @wc-ignore */ 'Email already registered.',
      translate: () => localize('Email already registered.'),
    },
    {
      match:
        /* @wc-ignore */ 'An email address is required for SSO authentication.',
      translate: () =>
        localize('An email address is required for SSO authentication.'),
    },
    {
      match:
        /* @wc-ignore */ 'Email must be verified by the SSO provider before signing in.',
      translate: () =>
        localize(
          'Email must be verified by the SSO provider before signing in.',
        ),
    },
    {
      match: /* @wc-ignore */ 'Invalid password.',
      translate: () => localize('Invalid password.'),
    },
    {
      match: /* @wc-ignore */ 'Invalid old password provided.',
      translate: () => localize('Invalid old password provided.'),
    },
    {
      match: /* @wc-ignore */ 'Invalid or expired refresh token.',
      translate: () => localize('Invalid or expired refresh token.'),
    },
    {
      match: /* @wc-ignore */ 'Invalid display name',
      translate: () => localize('Invalid display name'),
    },
    {
      match: /* @wc-ignore */ 'Display name already exist.',
      translate: () => localize('Display name already exist.'),
    },
    {
      match: /* @wc-ignore */ 'Invalid username',
      translate: () => localize('Invalid username'),
    },
    {
      match: /* @wc-ignore */ 'Username already exist.',
      translate: () => localize('Username already exist.'),
    },
    {
      match: /* @wc-ignore */ 'Invalid settings configuration.',
      translate: () => localize('Invalid settings configuration.'),
    },
    {
      match: /* @wc-ignore */ 'Invalid password min length',
      translate: () => localize('Invalid password min length'),
    },
    {
      match: /* @wc-ignore */ 'Invalid image max size',
      translate: () => localize('Invalid image max size'),
    },
    {
      match: /* @wc-ignore */ 'Your account is pending approval.',
      translate: () => localize('Your account is pending approval.'),
    },
    {
      match: /* @wc-ignore */ 'Sign up is not allowed',
      translate: () => localize('Sign up is not allowed'),
    },
    {
      match: /* @wc-ignore */ 'Invalid or expired OAuth state',
      translate: () => localize('Invalid or expired OAuth state'),
    },
    {
      match:
        /* @wc-ignore */ 'You cannot grant or revoke roles from yourself. Use CLI instead.',
      translate: () =>
        localize(
          'You cannot grant or revoke roles from yourself. Use CLI instead.',
        ),
    },
    {
      match: /* @wc-ignore */ 'You cannot change your own status.',
      translate: () => localize('You cannot change your own status.'),
    },
    {
      match: /* @wc-ignore */ 'Invalid user role',
      translate: () => localize('Invalid user role'),
    },
    {
      match: /* @wc-ignore */ 'This action is not allowed in demo mode',
      translate: () => localize('This action is not allowed in demo mode'),
    },
    {
      match: /* @wc-ignore */ 'Access denied',
      translate: () => localize('Access denied'),
    },
    {
      match: /* @wc-ignore */ 'Resource not found',
      translate: () => localize('Resource not found'),
    },
    {
      match: /* @wc-ignore */ 'An error occurred',
      translate: () => localize('An error occurred'),
    },
    {
      match: /* @wc-ignore */ 'An error occurred, while processing the request',
      translate: () =>
        localize('An error occurred, while processing the request'),
    },
    {
      match: /* @wc-ignore */ 'Validation Error',
      translate: () => localize('Validation Error'),
    },
    {
      match: /* @wc-ignore */ 'Specification Error',
      translate: () => localize('Specification Error'),
    },
    {
      match: /* @wc-ignore */ 'You cannot bookmark your own image',
      translate: () => localize('You cannot bookmark your own image'),
    },
    {
      match: /* @wc-ignore */ 'Cannot bookmark a private image',
      translate: () => localize('Cannot bookmark a private image'),
    },
    {
      match:
        /* @wc-ignore */ 'Comment edit window has expired. Comments can only be edited within 24 hours of creation.',
      translate: () =>
        localize(
          'Comment edit window has expired. Comments can only be edited within 24 hours of creation.',
        ),
    },
    {
      match: /* @wc-ignore */ 'You can only access your own collections',
      translate: () => localize('You can only access your own collections'),
    },
    {
      match: /* @wc-ignore */ 'You can only access your own tags',
      translate: () => localize('You can only access your own tags'),
    },
    {
      match: /* @wc-ignore */ 'Cannot move a tag to itself',
      translate: () => localize('Cannot move a tag to itself'),
    },
    {
      match: /* @wc-ignore */ 'Cannot move a tag to one of its descendants',
      translate: () => localize('Cannot move a tag to one of its descendants'),
    },
    {
      match: /* @wc-ignore */ 'Storage provider is not configured.',
      translate: () => localize('Storage provider is not configured.'),
    },
    {
      match: /* @wc-ignore */ 'SMB configuration is incomplete.',
      translate: () => localize('SMB configuration is incomplete.'),
    },
    {
      match:
        /* @wc-ignore */ 'Storage usage metrics are disabled for this provider',
      translate: () =>
        localize('Storage usage metrics are disabled for this provider'),
    },
    {
      match: /* @wc-ignore */ 'S3 bucket is required.',
      translate: () => localize('S3 bucket is required.'),
    },
    {
      match: /* @wc-ignore */ 'S3 region is required when using AWS.',
      translate: () => localize('S3 region is required when using AWS.'),
    },
    {
      match: /* @wc-ignore */ 'S3 Access key is required.',
      translate: () => localize('S3 Access key is required.'),
    },
    {
      match: /* @wc-ignore */ 'S3 Secret key is required.',
      translate: () => localize('S3 Secret key is required.'),
    },
    {
      match: /* @wc-ignore */ 'The expiry date must be in the future.',
      translate: () => localize('The expiry date must be in the future.'),
    },
    {
      match: /* @wc-ignore */ 'API key not found',
      translate: () => localize('API key not found'),
    },
    {
      match: /* @wc-ignore */ 'API key does not belong to user',
      translate: () => localize('API key does not belong to user'),
    },
    {
      match: /* @wc-ignore */ 'API key has expired',
      translate: () => localize('API key has expired'),
    },
    {
      match: /* @wc-ignore */ 'Validation failed',
      translate: () => localize('Validation failed'),
    },
    {
      match: /* @wc-ignore */ 'An unexpected error occurred',
      translate: () => localize('An unexpected error occurred'),
    },
    {
      match:
        /* @wc-ignore */ 'File too large. Please choose a smaller file and try again.',
      translate: () =>
        localize('File too large. Please choose a smaller file and try again.'),
    },
    {
      match: /* @wc-ignore */ 'Max size cannot be less than 0',
      translate: () => localize('Max size cannot be less than 0'),
    },
    {
      match: /* @wc-ignore */ 'Max size cannot be greater than 1000',
      translate: () => localize('Max size cannot be greater than 1000'),
    },
    {
      match: /* @wc-ignore */ 'Invalid tag ID format',
      translate: () => localize('Invalid tag ID format'),
    },
    {
      match: /* @wc-ignore */ 'Invalid collection ID format',
      translate: () => localize('Invalid collection ID format'),
    },
    {
      match: /* @wc-ignore */ 'Password length cannot be less than 3',
      translate: () => localize('Password length cannot be less than 3'),
    },
    {
      match: /* @wc-ignore */ 'Password length cannot be greater than 64',
      translate: () => localize('Password length cannot be greater than 64'),
    },
    {
      match: /* @wc-ignore */ 'This value should not be blank.',
      translate: () => localize('This value should not be blank.'),
    },
    {
      match: /* @wc-ignore */ 'This value is not a valid email address.',
      translate: () => localize('This value is not a valid email address.'),
    },
    {
      match: /* @wc-ignore */ 'This value is not a valid URL.',
      translate: () => localize('This value is not a valid URL.'),
    },
    {
      match: /* @wc-ignore */ 'This value is not a valid UUID.',
      translate: () => localize('This value is not a valid UUID.'),
    },
    {
      match: /* @wc-ignore */ 'This value should be positive.',
      translate: () => localize('This value should be positive.'),
    },
    {
      match: /* @wc-ignore */ 'This value is not valid.',
      translate: () => localize('This value is not valid.'),
    },
    {
      match: /* @wc-ignore */ 'The value you selected is not a valid choice.',
      translate: () =>
        localize('The value you selected is not a valid choice.'),
    },
    {
      match: /* @wc-ignore */ 'Invalid key format',
      translate: () => localize('Invalid key format'),
    },
    {
      match:
        /* @wc-ignore */ 'Tag name can only contain letters, numbers, hyphens, and underscores',
      translate: () =>
        localize(
          'Tag name can only contain letters, numbers, hyphens, and underscores',
        ),
    },
    {
      match:
        /* @wc-ignore */ 'Display name can only contain letters, numbers, underscores, hyphens, spaces, and periods.',
      translate: () =>
        localize(
          'Display name can only contain letters, numbers, underscores, hyphens, spaces, and periods.',
        ),
    },
    {
      match:
        /* @wc-ignore */ 'Display name cannot contain consecutive characters of the same type.',
      translate: () =>
        localize(
          'Display name cannot contain consecutive characters of the same type.',
        ),
    },
    {
      match: /* @wc-ignore */ '`Anonymous` is a reserved display name.',
      translate: () => localize('`Anonymous` is a reserved display name.'),
    },
    {
      match:
        /* @wc-ignore */ 'API key is required. Create one first from your profile page.',
      translate: () =>
        localize(
          'API key is required. Create one first from your profile page.',
        ),
    },
    {
      match: /* @wc-ignore */ 'The refresh token is required.',
      translate: () => localize('The refresh token is required.'),
    },
    {
      match: /* @wc-ignore */ 'Invalid license type.',
      translate: () => localize('Invalid license type.'),
    },
    {
      match: /* @wc-ignore */ 'Invalid landing page.',
      translate: () => localize('Invalid landing page.'),
    },
    {
      match: /* @wc-ignore */ 'Invalid default visibility.',
      translate: () => localize('Invalid default visibility.'),
    },
    {
      match:
        /^The file is too large \((.+)\)\. Allowed maximum size is (.+)\.$/,
      translate: (m) =>
        localize(
          'The file is too large ({size}). Allowed maximum size is {limit}.',
          { size: m[1], limit: m[2] },
        ),
    },
    {
      match: /^The mime type (.+) is not supported\./,
      translate: (m) =>
        localize('The mime type {type} is not supported.', { type: m[1] }),
    },
    {
      match: /^Password must be at least (\d+) characters? long\.$/,
      translate: (m) =>
        localize('Password must be at least {limit} characters long.', {
          limit: m[1],
        }),
    },
    {
      match: /^Username must be at least (\d+) characters? long\.$/,
      translate: (m) =>
        localize('Username must be at least {limit} characters long.', {
          limit: m[1],
        }),
    },
    {
      match: /^Username must be at most (\d+) characters? long\.$/,
      translate: (m) =>
        localize('Username must be at most {limit} characters long.', {
          limit: m[1],
        }),
    },
    {
      match: /^Display name must be at least (\d+) characters? long$/,
      translate: (m) =>
        localize('Display name must be at least {limit} characters long.', {
          limit: m[1],
        }),
    },
    {
      match: /^Display name must be at most (\d+) characters? long$/,
      translate: (m) =>
        localize('Display name must be at most {limit} characters long.', {
          limit: m[1],
        }),
    },
    {
      match: /^`(.+)` is a reserved username\.$/,
      translate: (m) =>
        localize('`{value}` is a reserved username.', { value: m[1] }),
    },
    {
      match: /^`(.+)` is a reserved display name$/,
      translate: (m) =>
        localize('`{name}` is a reserved display name', { name: m[1] }),
    },
    {
      match: /^This value should have (\d+) characters? or more\.$/,
      translate: (m) =>
        localize('This value should have {limit} characters or more.', {
          limit: m[1],
        }),
    },
    {
      match:
        /^This value is too long\. It should have (\d+) characters? or less\.$/,
      translate: (m) =>
        localize(
          'This value is too long. It should have {limit} characters or less.',
          { limit: m[1] },
        ),
    },
    {
      match: /^This value should be (\d+) or less\.$/,
      translate: (m) =>
        localize('This value should be {limit} or less.', { limit: m[1] }),
    },
    {
      match: /^This value should be between (.+) and (.+)\.$/,
      translate: (m) =>
        localize('This value should be between {min} and {max}.', {
          min: m[1],
          max: m[2],
        }),
    },
    {
      match: /^This value should be of type (.+)\.$/,
      translate: (m) =>
        localize('This value should be of type {type}.', { type: m[1] }),
    },
    {
      match: /^This collection should contain (\d+) elements? or less\.$/,
      translate: (m) =>
        localize('This collection should contain {limit} elements or less.', {
          limit: m[1],
        }),
    },
    {
      match: /^Tag "(.+)" already exists$/,
      translate: (m) => localize('Tag "{name}" already exists', { name: m[1] }),
    },
  ];
}

runtimeTranslator.registerTranslator((message) => {
  for (const { match, translate } of errors()) {
    if (typeof match === 'string') {
      if (match === message) return translate();
    } else {
      const result = message.match(match);
      if (result) return translate(result);
    }
  }

  return message;
});
