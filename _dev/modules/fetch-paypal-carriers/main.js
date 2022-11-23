const path = require("path");
const https = require("https");
const fs = require("fs");
const getHtmlDom = require("./get-html-dom-from-url");
const carrierParser = require("./carrier-parser");

function fetchPaypalCarrier(output) {
  getHtmlDom(
    "https://developer.paypal.com/docs/tracking/reference/carriers/",
    (error, html) => {
      if (error) return console.error(error);

      carrierParser(html, (error, carriers) => {
        if (error) return console.error(error);

        const writer = fs.createWriteStream(output);
        writer.write(JSON.stringify(carriers));
        writer.close();
        console.log('Carriers are fetched');
      });
    }
  );
}

const output = process.argv[2];

if (output !== undefined) {
  fetchPaypalCarrier(output);
}
