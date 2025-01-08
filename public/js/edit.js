const getCopyRawText = () => textarea.value;

const deleteElement = document.getElementById("delete");
if (deleteElement) {
  deleteElement.addEventListener("click", async (e) => {
    e.preventDefault();

    if (confirm("Do you really want to delete?")) {
      try {
        const response = await fetch(window.location.href, {
          body: JSON.stringify({ method: "delete" }),
          headers: { "Content-Type": "application/json" },
          method: "POST",
        });
        if (response.redirected) {
          window.location.href = response.url;
        } else if (response.ok) {
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

const uploadContent = async () => {
  if (content !== textarea.value) {
    const temp = textarea.value;
    try {
      const response = await fetch(window.location.href, {
        body: JSON.stringify({ method: "edit", text: temp }),
        headers: { "Content-Type": "application/json" },
        method: "POST",
      });
      if (response.redirected) {
        window.location.href = response.url;
      } else if (response.ok) {
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

let direction = window.innerWidth > 789 ? "horizontal" : "vertical";
let splitH = initSplit(["#textarea", "#markdown"], direction, "h");

window.addEventListener("resize", () => {
  const newDirection = window.innerWidth > 789 ? "horizontal" : "vertical";
  if (newDirection !== direction) {
    direction = newDirection;
    splitH.destroy();
    splitH = initSplit(["#textarea", "#markdown"], direction, "h");
  }
});

initSplit(["#editor", "#file-drop"], "vertical", "v");

const fileDropElement = document.getElementById("file-drop");
const inputFileElement = document.getElementById("input-file");
const filesElement = document.getElementById("files");
const loaderElement = document.getElementById("loader");

let dragCounter = 0;

fileDropElement.addEventListener("dragenter", (e) => {
  e.preventDefault();
  if (loaderElement.style.display === "none") {
    dragCounter++;
    fileDropElement.classList.add("files-drag-enter");
  }
});
fileDropElement.addEventListener("dragleave", (e) => {
  e.preventDefault();
  if (loaderElement.style.display === "none") {
    dragCounter--;
    if (dragCounter === 0) {
      fileDropElement.classList.remove("files-drag-enter");
    }
  }
});
fileDropElement.addEventListener("dragover", (e) => {
  e.preventDefault();
});
fileDropElement.addEventListener("drop", (e) => {
  e.preventDefault();
  if (loaderElement.style.display === "none") {
    dragCounter = 0;
    fileDropElement.classList.remove("files-drag-enter");
    uploadFile(e.dataTransfer.files, e.dataTransfer.items);
  }
});
document.getElementById("browse").addEventListener("click", (e) => {
  e.preventDefault();
  inputFileElement.click();
});
inputFileElement.addEventListener("change", (e) => {
  uploadFile(e.target.files);
  e.target.value = null;
});

const getFiles = async () => {
  try {
    loaderElement.style.display = "flex";

    const response = await fetch(window.location.href, {
      body: JSON.stringify({ method: "files" }),
      headers: { "Content-Type": "application/json" },
      method: "POST",
    });
    if (response.redirected) {
      window.location.href = response.url;
    } else if (response.ok) {
      const files = await response.json();

      while (filesElement.firstChild) {
        filesElement.removeChild(filesElement.lastChild);
      }

      files.forEach((file) => {
        filesElement.appendChild(createFileElement(file));
      });
    }
  } catch {
  } finally {
    loaderElement.style.display = "none";
  }
};

const createFileElement = (filename) => {
  const fileElement = document.createElement("div");
  fileElement.classList.add("file");

  const filenameElement = document.createElement("a");
  filenameElement.href = `/file/${getPathnameLastSegment()}/${filename}`;
  filenameElement.target = "_blank";
  filenameElement.innerText = filename;
  fileElement.appendChild(filenameElement);

  const svg = document.createElementNS(svgNS, "svg");
  svg.setAttribute("width", "1em");
  svg.setAttribute("height", "1em");
  svg.setAttribute("viewBox", "0 0 24 24");
  const path = document.createElementNS(svgNS, "path");
  path.setAttribute("fill", "currentColor");
  path.setAttribute(
    "d",
    "M19 6.41L17.59 5L12 10.59L6.41 5L5 6.41L10.59 12L5 17.59L6.41 19L12 13.41L17.59 19L19 17.59L13.41 12z"
  );
  svg.appendChild(path);

  const removeElement = document.createElement("button");
  removeElement.appendChild(svg);
  removeElement.addEventListener("click", async () => {
    if (confirm(`Do you really want to remove ${filename}?`)) {
      try {
        loaderElement.style.display = "flex";

        const response = await fetch(window.location.href, {
          body: JSON.stringify({ method: "fileRemove", filename: filename }),
          headers: { "Content-Type": "application/json" },
          method: "POST",
        });
        if (response.redirected) {
          window.location.href = response.url;
        } else if (response.ok) {
          getFiles();
        } else {
          throw new Error();
        }
      } catch {
        loaderElement.style.display = "none";
        alert(`Remove ${filename} failed!`);
      }
    }
  });

  fileElement.appendChild(removeElement);

  return fileElement;
};

getFiles();

const uploadFile = async (files, items) => {
  if (
    files.length !== 1 ||
    (items && items.length && items[0].webkitGetAsEntry().isDirectory)
  ) {
    alert("Please upload only one file!");
    return;
  }

  try {
    loaderElement.style.display = "flex";

    const formData = new FormData();
    formData.append("file", files[0]);
    const response = await fetch(window.location.href, {
      body: formData,
      method: "POST",
    });
    if (response.redirected) {
      window.location.href = response.url;
    } else if (response.ok) {
      getFiles();
    } else {
      throw new Error();
    }
  } catch {
    loaderElement.style.display = "none";
    alert("Upload failed!");
  }
};
