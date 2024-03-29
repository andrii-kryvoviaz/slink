export class IconRegistry {
  private readonly icons: Map<string, Set<string>>; // key: icon name, value: [filePath, ...]
  private readonly files: Map<string, Set<string>>; // key: filePath, value: [icon, ...]
  private version: number = 0;

  private constructor(icons: string[] = []) {
    this.icons = new Map(icons.map((icon) => [icon, new Set()]));
    this.files = new Map();
  }

  public static create(icons: string[]): IconRegistry {
    return new IconRegistry(icons);
  }

  public trackChanges(): void {
    this.version++;
  }

  private addLinkToMap(
    map: Map<string, Set<string>>,
    key: string,
    value: string
  ): void {
    if (!map.has(key)) {
      map.set(key, new Set());
    }
    map.get(key)?.add(value);
  }

  public addLink(icon: string, filePath: string): void {
    this.addLinkToMap(this.files, filePath, icon);
    this.addLinkToMap(this.icons, icon, filePath);
  }

  private removeLink(icon: string, filePath: string): void {
    this.icons.get(icon)?.delete(filePath);
    this.files.get(filePath)?.delete(icon);
    this.trackChanges();
  }

  public getIcons(): string[] {
    return Array.from(this.icons.keys());
  }

  public hasIcon(icon: string): boolean {
    return this.icons.has(icon);
  }

  public getIconsByFile(filePath: string): Set<string> {
    return this.files.get(filePath) || new Set();
  }

  public purgeUnusedLinks(filePath: string, parsedIcons: string[]): void {
    this.getIconsByFile(filePath).forEach((icon) => {
      if (!parsedIcons.includes(icon)) {
        this.removeLink(icon, filePath);
      }
    });
  }

  private getUnlinkedIcons(): string[] {
    return Array.from(this.icons.entries())
      .filter(([, files]) => files.size === 0)
      .map(([icon]) => icon);
  }

  private purgeUnusedIcons(): void {
    this.getUnlinkedIcons().forEach((icon) => {
      this.icons.delete(icon);
      this.trackChanges();
    });
  }

  public getPersistentIcons(): string[] {
    this.purgeUnusedIcons();

    return this.getIcons();
  }

  public hasChanged() {
    return this.version > 0;
  }
}
