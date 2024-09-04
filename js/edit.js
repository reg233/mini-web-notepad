const getCopyText = () => textarea.value;
const getDeleteUrl = () => window.location.href;

const uploadContent = async () => {
  if (content !== textarea.value) {
    const temp = textarea.value;
    try {
      const response = await fetch(window.location.href, {
        body: `text=${encodeURIComponent(temp)}`,
        headers: {
          "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
        },
        method: "POST",
      });
      if (response.ok) {
        if (temp === textarea.value) {
          statusElement.className = "status-success";
        }
        content = temp;
      } else {
        throw new Error();
      }
    } catch {
      statusElement.className = "status-danger";
    } finally {
      setTimeout(uploadContent, 1000);
    }
  } else {
    setTimeout(uploadContent, 1000);
  }
};

const statusElement = document.getElementById("status");
const textarea = document.getElementById("textarea");
let content = textarea.value;
const markdown = document.getElementById("markdown");

uploadContent();

const md = initMarkdownIt();
textarea.addEventListener("input", (e) => {
  if (content === textarea.value) {
    statusElement.className = "status-success";
  } else {
    statusElement.className = "status-attention";
  }
  markdown.innerHTML = md.render(e.target.value);
});
markdown.innerHTML = md.render(content);

const initSplit = () => {
  return Split(["#textarea", "#markdown"], {
    direction,
    gutter: (_, direction) => {
      const gutter = document.createElement("div");
      gutter.className = `gutter gutter-${direction}`;

      const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
      svg.setAttribute("width", "16");
      svg.setAttribute("height", "16");
      svg.setAttribute("fill", "currentColor");
      svg.setAttribute("viewBox", "0 0 16 16");
      const path = document.createElementNS(
        "http://www.w3.org/2000/svg",
        "path"
      );
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
      localStorage.setItem(splitSizesKey, JSON.stringify(e));
    },
  });
};

let direction = window.innerWidth > 789 ? "horizontal" : "vertical";
const splitSizesKey = `split-sizes${window.location.pathname}`;
let sizes = localStorage.getItem(splitSizesKey);
if (sizes) {
  sizes = JSON.parse(sizes);
} else {
  sizes = [50, 50];
}
let split = initSplit();

window.addEventListener("resize", () => {
  const newDirection = window.innerWidth > 789 ? "horizontal" : "vertical";
  if (newDirection !== direction) {
    direction = newDirection;
    split.destroy();
    split = initSplit();
  }
});
