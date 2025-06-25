export const faqQuestions = [
  {
    slug: 'supported-image-formats',
    title: 'What image formats Slink supports?',
    content: `Slink supports the following mime types:
      <div class="mt-2 w-full flex flex-wrap gap-2">
        <span class="badge badge-primary badge-outline">image/bmp</span>
        <span class="badge badge-primary badge-outline">image/png</span>
        <span class="badge badge-primary badge-outline">image/jpeg</span>
        <span class="badge badge-primary badge-outline">image/jpg</span>
        <span class="badge badge-primary badge-outline">image/gif</span>
        <span class="badge badge-primary badge-outline">image/webp</span>
        <span class="badge badge-primary badge-outline">image/svg+xml</span>
        <span class="badge badge-primary badge-outline">image/svg</span>
        <span class="badge badge-primary badge-outline">image/x-icon</span>
        <span class="badge badge-primary badge-outline">image/vnd.microsoft.icon</span>
        <span class="badge badge-primary badge-outline">image/x-tga</span>
        <span class="badge badge-primary badge-outline">image/avif</span>
        <span class="badge badge-primary badge-outline">image/heic *</span>
        <span class="badge badge-primary badge-outline">image/tiff *</span>
        <span class="badge badge-primary badge-outline">image/tif *</span>
      </div>
      <p class="mt-4 text-xs">
        <span class="font-light">* Source images are forced to be converted to JPEG format.</span>
      </p>
    `,
  },
  {
    slug: 'image-visability',
    title: 'What is the visibility of my images?',
    content: `Slink offers <a href="/explore" target="_blank" class="text-indigo-500 hover:underline">public gallery</a>, where people can share their images with others.
      <div class="my-2 flex flex-col gap-1 w-full">
        <p\>
          <svg class="inline" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 256 256"><path fill="currentColor" d="M247.31 124.76c-.35-.79-8.82-19.58-27.65-38.41C194.57 61.26 162.88 48 128 48S61.43 61.26 36.34 86.35C17.51 105.18 9 124 8.69 124.76a8 8 0 0 0 0 6.5c.35.79 8.82 19.57 27.65 38.4C61.43 194.74 93.12 208 128 208s66.57-13.26 91.66-38.34c18.83-18.83 27.3-37.61 27.65-38.4a8 8 0 0 0 0-6.5M128 192c-30.78 0-57.67-11.19-79.93-33.25A133.47 133.47 0 0 1 25 128a133.33 133.33 0 0 1 23.07-30.75C70.33 75.19 97.22 64 128 64s57.67 11.19 79.93 33.25A133.46 133.46 0 0 1 231.05 128c-7.21 13.46-38.62 64-103.05 64m0-112a48 48 0 1 0 48 48a48.05 48.05 0 0 0-48-48m0 80a32 32 0 1 1 32-32a32 32 0 0 1-32 32"/></svg> 
          <span class="font-bold mx-2">Public</span> - <span class="ml-2 font-light">Means that the image is shared in the public gallery.</span>
        </p>
        <p>
          <svg class="inline" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 256 256"><path fill="currentColor" d="M52.44 36a6 6 0 0 0-8.88 8l20.88 23c-37.28 21.9-53.23 57-53.92 58.57a6 6 0 0 0 0 4.88c.34.77 8.66 19.22 27.24 37.8C55 185.47 84.62 206 128 206a124.91 124.91 0 0 0 52.57-11.25l23 25.29a6 6 0 0 0 8.88-8.08Zm48.62 71.32l45 49.52a34 34 0 0 1-45-49.52M128 194c-31.38 0-58.78-11.42-81.45-33.93A134.57 134.57 0 0 1 22.69 128c4.29-8.2 20.1-35.18 50-51.91l20.2 22.21a46 46 0 0 0 61.35 67.48l17.81 19.6A113.47 113.47 0 0 1 128 194m6.4-99.4a6 6 0 0 1 2.25-11.79a46.17 46.17 0 0 1 37.15 40.87a6 6 0 0 1-5.42 6.53h-.56a6 6 0 0 1-6-5.45A34.1 34.1 0 0 0 134.4 94.6m111.08 35.85c-.41.92-10.37 23-32.86 43.12a6 6 0 1 1-8-8.94A134.07 134.07 0 0 0 233.31 128a134.67 134.67 0 0 0-23.86-32.07C186.78 73.42 159.38 62 128 62a120.19 120.19 0 0 0-19.69 1.6a6 6 0 1 1-2-11.83A131.12 131.12 0 0 1 128 50c43.38 0 73 20.54 90.24 37.76c18.58 18.58 26.9 37 27.24 37.81a6 6 0 0 1 0 4.88"/></svg>
          <span class="font-bold mx-2">Private</span> - <span class="ml-2 font-light">Means that the image is not shared in the public gallery.</span>
        </p>
      </div>
      
      <p class="mb-6">
        By default, all images are private and only accessible by the owner.
      </p>
      
      <span class="font-bold">Note:</span>
      Raw images are available for everyone, who has a direct link to the image.`,
  },
  {
    slug: 'share-feature',
    title: 'Can I share my images with others?',
    content: `Yes, you can share your images with others by using the direct link.
      <br>
      <br>
      If you need more control over the visibility of your images, like setting an expiration date, 
      password protection, or sharing within a specific group of people, please support the <a href="https://github.com/andrii-kryvoviaz/slink/issues/7" target="_blank" class="text-indigo-500 hover:underline">feature request</a> on our GitHub repository.`,
  },
  {
    slug: 'found-an-issue',
    title: 'I found an issue, how can I report it?',
    content: `If you found an issue, please report it on our GitHub repository. 
      <br>
      Visit the <a 
        class="text-indigo-500 hover:underline"
        href="https://github.com/andrii-kryvoviaz/slink/issues" target="_blank">issues page</a> 
      and create a new issue.`,
  },
];
