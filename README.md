# Slink: Image Sharing Platform

![GitHub Workflow Status (with event)](https://img.shields.io/github/actions/workflow/status/andrii-kryvoviaz/slink/release.yml?logo=github)
![Docker Image Version (latest semver)](https://img.shields.io/docker/v/anirdev/slink?color=blue)
![Docker Pulls](https://img.shields.io/docker/pulls/anirdev/slink?logo=docker)
[![License](https://img.shields.io/github/license/andrii-kryvoviaz/slink?color=blue
)](LICENSE)

![Slink](https://docs.slinkapp.io/_astro/7e68ebd6-a826-43ba-aaa7-ddb6b69357c1.CIkeYoem_1WfK3t.webp)

Welcome to **Slink**, a powerful self-hosted image sharing platform designed to give users complete control over their media sharing experience. Built with [Symfony](https://symfony.com/) and [SvelteKit](https://kit.svelte.dev/), Slink enables seamless and secure image sharing without relying on third-party services.

## Why Slink?

Slink solves the problem of sharing images with friends, family, and colleagues in a private, self-hosted environment. It's also ideal for:

- **Artists**: Showcase artwork in a community-focused platform.
- **Developers**: Host and share screenshots for GitHub, portfolios, blogs, and more.
- **Anyone**: Take control of image privacy and hosting.

## Features
- **Image Upload**: Supports _PNG_, _JPG_, _WEBP_, _SVG_, _BMP_, _ICO_, _GIF_, _AVIF_, _HEIC*_ and _TIFF*_.
- **Authentication**: Users can sign up and log in to the platform.
- **User Approval**: Require user approval before they can upload images.
- **Share Links**: Users can share links to their uploaded images and customize the image size.
- **Upload History**: Provides an overview of all images uploaded by the user.
- **Storage Providers**: Support for _local_, _SMB_, _AWS S3_ storage providers.
- **Explore Images**: Features a listing page of public images uploaded by other users.
- **Dark Mode**: Includes support for both _Dark_ and _Light_ modes in the application.
- **Dashboard**: Enhanced statistics and analytics for admin users.
- **Settings Configuration**: Ability to manage users, storage, and other settings.
- **Public API**: A public API to access the platform programmatically (Still needs to be documented).

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
