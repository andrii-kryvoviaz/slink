# Slink: Image Sharing Platform

![GitHub Workflow Status (with event)](https://img.shields.io/github/actions/workflow/status/andrii-kryvoviaz/slink/release.yml?logo=github)
![Docker Image Version (latest semver)](https://img.shields.io/docker/v/anirdev/slink?sort=semver&color=blue)
![Docker Pulls](https://img.shields.io/docker/pulls/anirdev/slink?logo=docker)
[![License](https://img.shields.io/github/license/andrii-kryvoviaz/slink?color=blue)](LICENSE)

![Slink](https://docs.slinkapp.io/_astro/d427de16-7a01-4ec7-aef9-913c0940a525.C8Cj-T6z_Z1EHyTU.webp)

Welcome to **Slink**, a powerful self-hosted image sharing platform designed to give users complete control over their media sharing experience. Built with [Symfony](https://symfony.com/) and [SvelteKit](https://kit.svelte.dev/), Slink enables seamless and secure image sharing without relying on third-party services.

**[Live Demo](https://demo.slinkapp.io)** | **[Documentation](https://docs.slinkapp.io/)**

## Why Slink?

Slink solves the problem of sharing images with friends, family, and colleagues in a private, self-hosted environment. It's also ideal for:

- **Artists**: Showcase artwork in a community-focused platform.
- **Developers**: Host and share screenshots for GitHub, portfolios, blogs, and more.
- **Anyone**: Take control of image privacy and hosting.

## Features

- **Image Upload**: Supports _PNG_, _JPG_, _WEBP_, _SVG_, _BMP_, _ICO_, _GIF_, _AVIF_, _HEIC\*_ and _TIFF\*_.
- **Multi-File Upload**: Upload multiple files simultaneously with progress tracking and error handling.
- **Image Compression**: Compress images to reduce file size and improve performance.
- **Share Links**: Users can share links to their uploaded images and customize the image size.
- **URL Shortening**: Shorten image URLs for easier sharing.
- **Upload History**: Provides an overview of all images uploaded by the user with both list and grid view options.
- **Explore Images**: Features a listing page of public images uploaded by other users.
- **Collections**: Create collections of images and share them with others.
- **Image Deduplication**: Automatic detection and handling of duplicate images with user-friendly notifications.
- **Nested Tags System**: Organize and filter images with hierarchical tag management, search capabilities, and dedicated management page.
- **Comments**: Leave comments on public images for discussion and feedback.
- **Bookmarking**: Bookmark public images for easy access later.
- **Notifications**: Receive notifications for user interactions with your images.
- **Authentication**: Users can sign up and log in to the platform.
- **User Approval**: Require user approval before they can upload images.
- **Guest Upload**: Allow unauthenticated users to upload images without creating accounts.
- **API Key Management**: Generate and manage personal API keys for external integrations.
- **Public API**: A public API to access the platform programmatically.
- **ShareX Integration**: Seamless integration with ShareX for automatic screenshot uploads.
- **Storage Providers**: Support for _local_, _SMB_, _AWS S3_ storage providers.
- **Storage Usage Tracking**: Monitor and display storage consumption metrics.
- **Admin Image Management**: Full administrative control over image visibility and content moderation.
- **Dashboard**: Enhanced statistics and analytics for admin users.
- **Settings Configuration**: Ability to manage users, storage, and other settings.
- **Dark Mode**: Includes support for both _Dark_ and _Light_ modes in the application.

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
