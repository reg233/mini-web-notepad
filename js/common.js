const copyElement = document.getElementById("copy");
if (copyElement) {
  copyElement.addEventListener("click", (e) => {
    e.preventDefault();

    if (copyElement.innerText === "Copy") {
      navigator.clipboard.writeText(getCopyText());
      copyElement.innerText = "Copied!";
      setTimeout(() => {
        copyElement.innerText = "Copy";
      }, 1000);
    }
  });
}

const deleteElement = document.getElementById("delete");
if (deleteElement) {
  deleteElement.addEventListener("click", async (e) => {
    e.preventDefault();

    if (confirm("Do you really want to delete?")) {
      try {
        const response = await fetch(getDeleteUrl(), {
          body: JSON.stringify({ method: "delete" }),
          headers: { "Content-Type": "application/json" },
          method: "POST",
        });
        if (response.ok) {
          window.open("/", "_self");
        } else {
          throw new Error();
        }
      } catch {
        alert("Delete failed!");
      }
    }
  });
}

const initMarkdownIt = () => {
  const md = window
    .markdownit({ html: true, linkify: true })
    .use(window.markdownitTaskLists);

  // https://github.com/markdown-it/markdown-it/blob/master/docs/architecture.md#renderer
  // Remember the old renderer if overridden, or proxy to the default renderer.
  const defaultRender =
    md.renderer.rules.link_open ||
    ((tokens, idx, options, _, self) => {
      return self.renderToken(tokens, idx, options);
    });
  // Add target="_blank" to all other links.
  md.renderer.rules.link_open = (tokens, idx, options, env, self) => {
    try {
      const href = new Map(tokens[idx].attrs).get("href");
      if (
        href.startsWith("/file/") ||
        new URL(href).origin !== window.location.origin
      ) {
        // Add a new `target` attribute, or replace the value of the existing one.
        tokens[idx].attrSet("target", "_blank");
      }
    } catch {}

    // Pass the token to the default renderer.
    return defaultRender(tokens, idx, options, env, self);
  };

  return md;
};

const getPathnameLastSegment = () => {
  const segments = window.location.pathname.split("/");
  return segments.pop() || segments.pop();
};

const svgNS = "http://www.w3.org/2000/svg";

const initSplit = (elements, direction, key) => {
  const storageKey = `split-${key}-${getPathnameLastSegment()}`;

  let sizes = localStorage.getItem(storageKey);
  sizes = sizes ? JSON.parse(sizes) : key === "h" ? [50, 50] : [80, 20];

  return Split(elements, {
    direction,
    gutter: (_, direction) => {
      const gutter = document.createElement("div");
      gutter.className = `gutter gutter-${direction}`;

      const svg = document.createElementNS(svgNS, "svg");
      svg.setAttribute("width", "16");
      svg.setAttribute("height", "16");
      svg.setAttribute("fill", "currentColor");
      svg.setAttribute("viewBox", "0 0 16 16");
      const path = document.createElementNS(svgNS, "path");
      if (direction === "horizontal") {
        path.setAttribute(
          "d",
          "M7 2a1 1 0 1 1-2 0 1 1 0 0 1 2 0m3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0M7 5a1 1 0 1 1-2 0 1 1 0 0 1 2 0m3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0M7 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0m3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0m-3 3a1 1 0 1 1-2 0 1 1 0 0 1 2 0m3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0m-3 3a1 1 0 1 1-2 0 1 1 0 0 1 2 0m3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0"
        );
      } else {
        path.setAttribute(
          "d",
          "M2 8a1 1 0 1 1 0 2 1 1 0 0 1 0-2m0-3a1 1 0 1 1 0 2 1 1 0 0 1 0-2m3 3a1 1 0 1 1 0 2 1 1 0 0 1 0-2m0-3a1 1 0 1 1 0 2 1 1 0 0 1 0-2m3 3a1 1 0 1 1 0 2 1 1 0 0 1 0-2m0-3a1 1 0 1 1 0 2 1 1 0 0 1 0-2m3 3a1 1 0 1 1 0 2 1 1 0 0 1 0-2m0-3a1 1 0 1 1 0 2 1 1 0 0 1 0-2m3 3a1 1 0 1 1 0 2 1 1 0 0 1 0-2m0-3a1 1 0 1 1 0 2 1 1 0 0 1 0-2"
        );
      }
      svg.appendChild(path);

      gutter.appendChild(svg);

      return gutter;
    },
    gutterSize: 16,
    sizes,
    onDragEnd: (e) => {
      localStorage.setItem(storageKey, JSON.stringify(e));
    },
  });
};
