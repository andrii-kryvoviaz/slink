import fs from 'fs';

import { RegexHelper } from '../helpers';
import { IconRegistry } from './IconRegistry';

export class IconFile {
  private iconRegistry: IconRegistry;
  private readonly variableDeclaration: string;

  private constructor(private readonly filePath: string) {
    const content = fs.readFileSync(this.filePath, 'utf-8');
    this.iconRegistry = IconRegistry.create(
      RegexHelper.extractRegexGroupsToArray('ICON_LINE', content)
    );
    this.variableDeclaration = RegexHelper.matchString(
      'VARIABLE_DECLARATION',
      content,
      {
        defaultValue: 'export const icons',
        group: 0,
      }
    );
  }

  public static create(filePath: string): IconFile {
    return new IconFile(filePath);
  }

  public processTemplate(filePath: string): void {
    if (filePath === this.filePath) {
      return;
    }

    const content = fs.readFileSync(filePath, 'utf-8');
    const parsedIcons = RegexHelper.extractRegexGroupsToArray(
      'ICON_TAG',
      content,
      {
        group: 1,
      }
    );

    console.log(parsedIcons);

    if (!parsedIcons.length) {
      return;
    }

    parsedIcons.forEach((icon) => {
      if (!this.iconRegistry.hasIcon(icon)) {
        this.iconRegistry.trackChanges();
      }
      this.iconRegistry.addLink(icon, filePath);
    });

    this.iconRegistry.purgeUnusedLinks(filePath, parsedIcons);
  }

  persist() {
    const icons = this.iconRegistry.getPersistentIcons();
    const content = `${this.variableDeclaration} = [\n  '${icons.join(
      "',\n  '"
    )}',\n];\n`;
    if (this.iconRegistry.hasChanged()) {
      fs.writeFileSync(this.filePath, content);
    }
  }
}
