/**
 * 2007-2023 PayPal
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 *  versions in the future. If you wish to customize PrestaShop for your
 *  needs please refer to http://www.prestashop.com for more information.
 *
 *  @author 2007-2023 PayPal
 *  @author 202 ecommerce <tech@202-ecommerce.com>
 *  @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *  @copyright PayPal
 *
 */
const path = require('path');
const FixStyleOnlyEntriesPlugin = require('webpack-fix-style-only-entries');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const TerserPlugin = require('terser-webpack-plugin');

const minimizers = [];
const plugins = [
  new FixStyleOnlyEntriesPlugin(),
  new MiniCssExtractPlugin({
    filename: '[name].css',
  }),
];

const config = {
  entry: {
    'js/bo_order': './202/_dev/js/bo_order.js',
    'js/order_confirmation': './202/_dev/js/order_confirmation.js',
    'js/payment_ppp': './202/_dev/js/payment_ppp.js',
    'js/shortcut_payment': './202/_dev/js/shortcut_payment.js',
    'js/shortcut': './202/_dev/js/shortcut.js',
    'js/bnpl': './202/_dev/js/bnpl.js',
    'js/payment_mb': './202/_dev/js/payment_mb.js',
    'js/paypal-info': './202/_dev/js/paypal-info.js',
    'js/Venmo': './202/_dev/js/Venmo.js',
    'js/apmButton': './202/_dev/js/apmButton.js',
    'js/sepaButton': './202/_dev/js/sepaButton.js',
    'js/acdc': './202/_dev/js/acdc.js',
    'js/tools': './202/_dev/js/tools.js',
    'js/diagnostic/diagnostic': './202/_dev/js/diagnostic/diagnostic.js',
    'js/admin': './202/_dev/js/admin.js',

    'css/paypal_bo': './202/_dev/scss/paypal_bo.scss',
    'css/paypal_fo': './202/_dev/scss/paypal_fo.scss',
    'css/diagnostic/diagnostic': './202/_dev/scss/diagnostic/diagnostic.scss',
  },

  output: {
    filename: '[name].js',
    path: path.resolve(__dirname, './views/'),
  },

  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: [
          {
            loader: 'babel-loader',
            options: {
              presets: ['@babel/preset-env'],
            },
          },
        ],
      },

      {
        test: /\.(s)?css$/,
        use: [
          {loader: MiniCssExtractPlugin.loader},
          {loader: 'css-loader'},
          {loader: 'postcss-loader'},
          {loader: 'sass-loader'},
        ],
      },
      {
        // Extract fonts in `/assets/fonts/`.
        test: /.(woff(2)?|eot|ttf)(\?[a-z0-9=.]+)?$/,
        type:'asset/resource',
        generator: {
          filename: "fonts/[name][ext]"
        },
      }
    ],
  },

  externals: {
    $: '$',
    jquery: 'jQuery',
  },

  plugins,

  optimization: {
    minimizer: minimizers,
  },

  resolve: {
    extensions: ['.js', '.scss', '.css'],
    alias: {
      '~': path.resolve(__dirname, './node_modules'),
      '$img_dir': path.resolve(__dirname, './views/img'),
    },
  },
};

module.exports = (env, argv) => {
  // Production specific settings
  if (argv.mode === 'production') {
    const terserPlugin = new TerserPlugin({
      // Remove comments except those containing @preserve|@license|@cc_on
      extractComments: /^\**!|@preserve|@license|@cc_on/i,
      parallel: true,
      terserOptions: {
        compress: {
          pure_funcs: [
            'console.log'
          ]
        }
      },
    });


    config.optimization.minimizer.push(terserPlugin);
  }

  return config;
};
