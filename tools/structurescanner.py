import pathlib

IGNORE_DIRS = {"vendor", "node_modules", ".git"}

def generate_tree(path, prefix=""):
    path = pathlib.Path(path)
    if not path.exists():
        return

    space = '    '
    branch = '│   '
    tee = '├── '
    last = '└── '

    contents = sorted(
        list(path.iterdir()),
        key=lambda x: (x.is_file(), x.name.lower())
    )
    contents = [
        entry for entry in contents
        if not (entry.is_dir() and entry.name in IGNORE_DIRS)
    ]

    pointers = [tee] * (len(contents) - 1) + [last] if contents else []

    for pointer, entry in zip(pointers, contents):
        print(f"{prefix}{pointer}{entry.name}")
        if entry.is_dir():
            extension = branch if pointer == tee else space
            generate_tree(entry, prefix=prefix + extension)

if __name__ == "__main__":
    root_dir = pathlib.Path.cwd()
    generate_tree(root_dir)