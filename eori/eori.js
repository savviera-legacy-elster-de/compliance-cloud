const puppeteer = require('puppeteer');

const request = require('request');
var http = require('http'); 

var https = require('https');
var p1 = process.argv[2];
var p2 = process.argv[3];





const fs = require('fs')
const path = require('path')
const { promisify } = require('util')

const readFileAsync = promisify(fs.readFile)
const writeFileAsync = promisify(fs.writeFile);

var file = fs.createWriteStream(p2+'.pdf');


(async () => {

  const browser = await puppeteer.launch();

  const page = await browser.newPage();





await page.setViewport({width: 1000, height: 1000})


  await page.goto('https://www.formulare-bfinv.de/ffw/action/invoke.do?id=0870a', {waitUntil: 'networkidle2'});
page.once('load', () => console.log('Page loaded!'));

await page.click('#datenschutz > a');

await page.waitFor(5000);


var data = await page._client.send('Network.getAllCookies');
console.log(data);


await page.click('[id="lip_toolbar_form:2fimportXMLData"]');

await page.waitFor(5000);





const [fileChooser] = await Promise.all([
  page.waitForFileChooser(),
  page.click('[name="xmlFile"]'),
]);
await fileChooser.accept(['/puppeteer/'+p1+'.xml']);


await page.waitFor(5000);

await page.click('[name="$action:importNewForm"]');


await page.waitFor(5000);


await page.click('[id="lip_toolbar_form:2fprint"]');
await page.waitFor(5000);

const pdfHref = await page.evaluate((sel) => {
		return document.querySelector(sel).getAttribute('href');
	}, '[title="(wird in einem separaten Fenster geöffnet)"]');


var c = await page.cookies();
let s = [];
c.forEach(element => { 
s.push(element.name + "=" + element.value);
  console.log(element); 
}); 
let ss = s.join('; ');
console.log(ss);
console.log(c);





	console.log("The file was saved!");
	console.log(pdfHref);









var options = {
  hostname: 'www.formulare-bfinv.de',
  port: 443,
  path: pdfHref,
  method: 'GET',
  headers: {'Cookie': ss, 'Accept-Encoding': 'gzip, deflate, br'}
};




var req = https.request(options, function(res) {
  console.log('STATUS: ' + res.statusCode);
  console.log('HEADERS: ' + JSON.stringify(res.headers));
res.pipe(file);







});

req.on('error', function(e) {
  console.log('problem with request: ' + e.message);
});

req.end();








  await page.screenshot({path: 'example.png'});



  const dimensions = await page.evaluate(() => {
    return {
      width: document.documentElement.clientWidth,
      height: document.documentElement.clientHeight,
      deviceScaleFactor: window.devicePixelRatio
    };
  });

  await browser.close();
})();
