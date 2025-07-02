<script lang="ts">
  import { ScrollArea } from '@slink/components/UI/ScrollArea';

  let scrollType: 'hover' | 'scroll' | 'auto' | 'always' = $state('scroll');
  let variant: 'default' | 'minimal' | 'modern' | 'glass' = $state('minimal');
  const demoContent = Array.from(
    { length: 20 },
    (_, i) =>
      `Item ${i + 1}: Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.`,
  );
</script>

<div class="space-y-8 p-6">
  <div>
    <h3 class="text-lg font-semibold mb-4">
      Platform-Agnostic ScrollArea Demo
    </h3>
    <div class="grid grid-cols-2 gap-4 mb-6">
      <div>
        <label for="scroll-type-select" class="block text-sm font-medium mb-2"
          >Scroll Type:</label
        >
        <select
          id="scroll-type-select"
          bind:value={scrollType}
          class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800"
        >
          <option value="hover">Hover (shows on hover)</option>
          <option value="scroll">Scroll (macOS style)</option>
          <option value="auto">Auto (browser default)</option>
          <option value="always">Always (always visible)</option>
        </select>
      </div>
      <div>
        <label for="variant-select" class="block text-sm font-medium mb-2"
          >Variant:</label
        >
        <select
          id="variant-select"
          bind:value={variant}
          class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800"
        >
          <option value="default">Default</option>
          <option value="minimal">Minimal</option>
          <option value="modern">Modern</option>
          <option value="glass">Glass</option>
        </select>
      </div>
    </div>
  </div>
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div
      class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-white dark:bg-gray-900"
    >
      <h4 class="text-md font-medium mb-3">Current Settings</h4>
      <ScrollArea
        {variant}
        type={scrollType}
        size="md"
        class="h-64 w-full"
        viewportPadding="sm"
        hideDelay={300}
      >
        <div class="space-y-3">
          {#each demoContent as content, i (i)}
            <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-md text-sm">
              {content}
            </div>
          {/each}
        </div>
      </ScrollArea>
    </div>
    <div class="space-y-4">
      <div
        class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-white dark:bg-gray-900"
      >
        <h4 class="text-md font-medium mb-3">Minimal Variant</h4>
        <ScrollArea
          variant="minimal"
          type="scroll"
          size="sm"
          viewportPadding="sm"
          hideDelay={200}
        >
          <div class="space-y-2">
            {#each demoContent.slice(0, 8) as content, i (i)}
              <div class="p-2 bg-gray-50 dark:bg-gray-800 rounded text-xs">
                {content.slice(0, 80)}...
              </div>
            {/each}
          </div>
        </ScrollArea>
      </div>
      <div
        class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-white dark:bg-gray-900"
      >
        <h4 class="text-md font-medium mb-3">Modern Variant</h4>
        <ScrollArea
          variant="modern"
          type="auto"
          size="sm"
          viewportPadding="sm"
          scrollbarSize="md"
        >
          <div class="space-y-2">
            {#each demoContent.slice(0, 8) as content, i (i)}
              <div class="p-2 bg-gray-50 dark:bg-gray-800 rounded text-xs">
                {content.slice(0, 80)}...
              </div>
            {/each}
          </div>
        </ScrollArea>
      </div>
    </div>
  </div>
  <div class="text-sm text-gray-600 dark:text-gray-400">
    <h4 class="font-medium mb-2">ScrollArea Features:</h4>
    <ul class="space-y-1 list-disc list-inside">
      <li><strong>hover:</strong> Scrollbars appear only on hover</li>
      <li>
        <strong>scroll:</strong> macOS-style scrollbars that fade after scrolling
      </li>
      <li><strong>auto:</strong> Browser default scrollbar behavior</li>
      <li><strong>always:</strong> Scrollbars are always visible</li>
      <li><strong>minimal:</strong> Ultra-thin, subtle scrollbars</li>
      <li>
        <strong>modern:</strong> Rounded scrollbars with borders and shadows
      </li>
      <li><strong>glass:</strong> Translucent scrollbars with backdrop blur</li>
    </ul>
  </div>
</div>
