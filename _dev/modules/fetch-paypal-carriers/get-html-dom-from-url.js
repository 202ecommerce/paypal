const https = require("https");
const jsdom = require("jsdom");

module.exports = (url, callback) => {

  https.get(
    url,
    (response) => {
      let rawData = '';
      response.setEncoding('utf-8');

      response.on('data', (chunk) => {
        rawData += chunk;
      });

      response.on('end', () => {
        let dom;

        try {
          dom = new jsdom.JSDOM(rawData);
        } catch (error) {
          callback(error, dom);
        }

        callback(null, dom);
      });
    }
  );
}
