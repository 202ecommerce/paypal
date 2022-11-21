const jsdom = require("jsdom");

module.exports = (html, callback) => {
  if (html instanceof jsdom.JSDOM === false) return;

  const carriers = {};

  html.window.document.querySelectorAll('tr').forEach((tr) => {
    const data = {
      key: null,
      name: null,
      domain: null,
    };
    tr.querySelectorAll("td").forEach((td, index) => {
      let value = td.textContent;
      switch (index) {
        case 0:
          data.name = value;
          break;
        case 1:
          data.key = value;
          break;
        case 2:
          data.domain = value.toUpperCase();
          break;
      }
    });

    if (data.domain === null) {
      return;
    }

    if (data.domain in carriers === false) {
      carriers[data.domain] = [];
    }

    carriers[data.domain].push({ key: data.key, name: data.name});
  });

  callback(null, carriers);
}
