

import 'babel-polyfill';

import '~/prestakit/dist/js/prestashop-ui-kit';
import Common from './src/Classes/Common';
import Logs from './src/Classes/Logs';

$(() => {
  const common = new Common();
  common.init();

  const logs = new Logs();
  logs.init();
});
