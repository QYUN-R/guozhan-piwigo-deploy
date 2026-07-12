(function () {
  "use strict";

  function formatSize(bytes) {
    if (bytes < 1024 * 1024) return Math.max(1, Math.round(bytes / 1024)) + " KB";
    return (bytes / 1024 / 1024).toFixed(1) + " MB";
  }

  function renderFiles(input, target) {
    if (!input || !target) return;
    target.innerHTML = "";
    var files = Array.prototype.slice.call(input.files || []);
    input.setCustomValidity(files.length > 20 ? "一次最多上传 20 张图片，请分批上传。" : "");
    files.slice(0, 20).forEach(function (file) {
      var row = document.createElement("span");
      var name = document.createElement("b");
      var size = document.createElement("small");
      name.textContent = file.name;
      size.textContent = formatSize(file.size);
      row.appendChild(name);
      row.appendChild(size);
      target.appendChild(row);
    });
    if (files.length > 20) {
      var warning = document.createElement("span");
      warning.className = "is-error";
      warning.textContent = "已选择 " + files.length + " 张，一次最多上传 20 张，请减少后再提交。";
      target.appendChild(warning);
    }
  }

  function initUpload() {
    var input = document.querySelector("[data-file-input]");
    var list = document.querySelector("[data-file-list]");
    var dropzone = document.querySelector("[data-dropzone]");
    var category = document.querySelector("[data-upload-category]");
    var prefix = document.querySelector("[data-code-prefix]");
    var selection = document.querySelector("[data-upload-selection]");
    var path = document.querySelector("[data-upload-path]");

    if (input) input.addEventListener("change", function () { renderFiles(input, list); });

    if (dropzone && input) {
      ["dragenter", "dragover"].forEach(function (eventName) {
        dropzone.addEventListener(eventName, function (event) {
          event.preventDefault();
          dropzone.classList.add("is-dragging");
        });
      });
      ["dragleave", "drop"].forEach(function (eventName) {
        dropzone.addEventListener(eventName, function (event) {
          event.preventDefault();
          dropzone.classList.remove("is-dragging");
        });
      });
      dropzone.addEventListener("drop", function (event) {
        if (event.dataTransfer && event.dataTransfer.files.length) {
          input.files = event.dataTransfer.files;
          renderFiles(input, list);
        }
      });
    }

    if (category && prefix) {
      var syncCategory = function () {
        var option = category.options[category.selectedIndex];
        var nextPrefix = option ? option.getAttribute("data-prefix") : "";
        var nextPath = option ? option.getAttribute("data-path") : "";
        prefix.value = nextPrefix || "";
        if (selection && path) {
          selection.hidden = !nextPath;
          path.textContent = nextPath ? nextPath + "，编号前缀 " + nextPrefix : "";
        }
      };
      category.addEventListener("change", syncCategory);
      syncCategory();
    }
  }

  function initConfirmations() {
    document.querySelectorAll("form").forEach(function (form) {
      var action = form.querySelector("input[name='gzca_action']");
      if (!action) return;

      form.addEventListener("submit", function (event) {
        if (action.value === "toggle_work") {
          var button = form.querySelector("button[type='submit']");
          var label = button ? button.textContent.trim() : "修改状态";
          if (!window.confirm("确定要" + label + "这张作品吗？")) {
            event.preventDefault();
            return;
          }
        }

        if (!event.defaultPrevented) {
          var submit = form.querySelector("button[type='submit']");
          if (submit) {
            submit.disabled = true;
            submit.dataset.originalText = submit.textContent;
            submit.textContent = action.value === "upload_works" ? "正在上传…" : "正在保存…";
          }
        }
      });
    });
  }

  function initCompetitionForm() {
    var slug = document.querySelector("input[name='slug']");
    if (!slug) return;
    slug.addEventListener("input", function () {
      slug.value = slug.value.toLowerCase().replace(/[^a-z0-9-]/g, "");
    });
  }

  function initCategoryForm() {
    var form = document.querySelector("[data-category-form]");
    if (!form) return;
    var parent = form.querySelector("[data-category-parent]");
    var kind = form.querySelector("[data-category-kind]");
    var prefix = form.querySelector("input[name='code_prefix']");
    var directUpload = form.querySelector("input[name='direct_upload']");

    if (parent && kind) {
      parent.addEventListener("change", function () {
        var option = parent.options[parent.selectedIndex];
        if (option && option.getAttribute("data-kind") === "exhibition") kind.value = "exhibition";
        if (directUpload) directUpload.checked = Boolean(parent.value);
      });
    }
    if (prefix) {
      prefix.addEventListener("input", function () {
        prefix.value = prefix.value.toUpperCase().replace(/[^A-Z0-9-]/g, "");
      });
    }
  }

  function initWorkFilters() {
    var form = document.querySelector("[data-work-filter-form]");
    if (!form) return;
    form.querySelectorAll("[data-auto-filter]").forEach(function (select) {
      select.addEventListener("change", function () {
        if (typeof form.requestSubmit === "function") form.requestSubmit();
        else form.submit();
      });
    });
  }

  document.addEventListener("DOMContentLoaded", function () {
    initUpload();
    initConfirmations();
    initCompetitionForm();
    initCategoryForm();
    initWorkFilters();
  });
})();
