# Slink: Image Sharing Platform

![GitHub Workflow Status (with event)](https://img.shields.io/github/actions/workflow/status/andrii-kryvoviaz/slink/release.yml?logo=github)
![Docker Image Version (latest semver)](https://img.shields.io/docker/v/anirdev/slink?sort=semver&color=blue)
![Docker Pulls](https://img.shields.io/docker/pulls/anirdev/slink?logo=docker)
[![License](https://img.shields.io/github/license/andrii-kryvoviaz/slink?color=blue)](LICENSE)

![Slink](https://docs.slinkapp.io/_astro/9e8b1f68-43f1-4488-99d1-7fdcd8642a65.BdT3PWp__20eaaR.webp)

Welcome to **Slink**, a powerful self-hosted image sharing platform designed to give users complete control over their media sharing experience. Built with [Symfony](https://symfony.com/) and [SvelteKit](https://kit.svelte.dev/), Slink enables seamless and secure image sharing without relying on third-party services.

**[Live Demo](https://demo.slinkapp.io)** | **[Documentation](https://docs.slinkapp.io/)**

## Why Slink?

Slink solves the problem of sharing images with friends, family, and colleagues in a private, self-hosted environment. It's also ideal for:

- **Artists**: Showcase artwork in a community-focused platform.
- **Developers**: Host and share screenshots for GitHub, portfolios, blogs, and more.
- **Anyone**: Take control of image privacy and hosting.

## Features

### Sharing & Access Control
- **Privacy-First Shares**: All shares are private by default; access is granted only to whoever holds the link.
- **Password-Protected Shares**: Optionally require a password to view a shared image.
- **Expiring Shares**: Set an expiration so a share auto-revokes after a chosen time.
- **Share Management**: Dedicated page to list, edit, publish, or revoke your shares.
- **URL Shortening**: Shorten image URLs for easier sharing.
- **Collections**: Group images and share an entire collection via a single link.
- **Explore**: Browse images other users have made publicly visible.

### Uploads & Media
- **Image Upload**: Supports _PNG_, _JPG_, _WEBP_, _SVG_, _BMP_, _ICO_, _GIF_, _AVIF_, _HEIC\*_ and _TIFF\*_.
- **Multi-File Upload**: Parallel uploads with progress tracking and per-file error handling.
- **Image Compression**: Compress images to reduce file size and improve performance.
- **Deduplication on Upload**: Detects duplicate images at upload time and notifies the user.
- **Guest Upload**: Allow unauthenticated users to upload images without creating accounts.
- **ShareX Integration**: Seamless integration with ShareX for automatic screenshot uploads.

### Organization
- **Nested Tags**: Hierarchical tag management with search capabilities and a dedicated management page.
- **Upload History**: Overview of all your uploads with list and grid views.
- **Bookmarks**: Save public images for easy access later.
- **Comments**: Leave comments on public images for discussion and feedback.
- **Notifications**: Receive notifications for user interactions with your images.

### Authentication & API
- **Authentication**: Email/password sign-up and login.
- **SSO / OIDC**: Single Sign-On with support for Google, Authentik, Keycloak, Authelia, Pocket ID, and custom OIDC providers.
- **User Approval**: Require user approval before they can upload images.
- **API Keys**: Generate and manage personal API keys for external integrations.
- **Public API**: A public API to access the platform programmatically.

### Personalization
- **Multi-Language UI**: Available in English, German, Spanish, French, Italian, Japanese, Polish, Ukrainian, and Chinese.
- **Dark Mode**: Includes support for both _Dark_ and _Light_ modes in the application.

### Administration
- **Admin Dashboard**: Enhanced statistics and analytics for admin users.
- **User & Settings Management**: Manage users, storage, and other platform settings.
- **Image Moderation**: Full administrative control over image visibility and content moderation.
- **Storage Providers**: Support for _local_, _SMB_, and _AWS S3_ storage providers.
- **Storage Usage Tracking**: Monitor and display storage consumption metrics.

Don't see a feature you need? Feel free to [open an issue](https://github.com/andrii-kryvoviaz/slink/issues/new) or [contribute](#contributing) to the project.
You may also want to check the list of [upcoming features](https://docs.slinkapp.io/getting-started/01-introduction/#upcoming-features).

## Documentation

- **[Installation](https://docs.slinkapp.io/getting-started/02-quick-start/)**
- **[Configuration](https://docs.slinkapp.io/configuration/01-environment-variables/)**
- **[Storage Providers Support](https://docs.slinkapp.io/reference/03-storage-provider/)**
- **[Security Considerations](https://docs.slinkapp.io/security/)**

For more information, visit the [official documentation](https://docs.slinkapp.io/).

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.
Any contributions you make are **greatly appreciated**, whether they are new features, bug fixes, or code quality improvements.

## License

This project is licensed under the AGPLv3 License. See the [LICENSE](LICENSE) file for details.
