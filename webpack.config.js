const path = require("path");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const { PurgeCSSPlugin } = require("purgecss-webpack-plugin");
const glob = require("glob");
const { CleanWebpackPlugin } = require("clean-webpack-plugin");
const CopyPlugin = require("copy-webpack-plugin");
const fs = require("fs");
const archiver = require("archiver");

module.exports = {
  entry: {
    main: "./js/copyPrivateLink.js",
    toastify: "./node_modules/toastify-js/src/toastify.js",
    sendNonce: "./js/sendNonce.js",
  },
  output: {
    filename: "js/[name].js",
    path: path.resolve(__dirname, "smart-link"),
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: "babel-loader",
          options: {
            presets: ["@babel/preset-env"],
          },
        },
      },
      {
        test: /\.css$/,
        use: [MiniCssExtractPlugin.loader, "css-loader"],
      },
    ],
  },
  plugins: [
    new CopyPlugin({
      patterns: [
        { from: "classes/sl_columns.php", to: "classes/sl_columns.php" },
        {
          from: "images/smartlinklogo-512-alpha.png",
          to: "images/smartlinklogo-512-alpha.png",
        },
        { from: "includes/sl_activate.php", to: "includes/sl_activate.php" },
        {
          from: "includes/sl_ajax_handler.php",
          to: "includes/sl_ajax_handler.php",
        },
        { from: "pages/slp_dashboard.php", to: "pages/slp_dashboard.php" },
        {
          from: "includes/sl_check_access_rewrite.php",
          to: "includes/sl_check_access_rewrite.php",
        },
        { from: "smart-link.php", to: "smart-link.php" },
        { from: "README.txt", to: "README.txt" },
      ],
    }),
    new CleanWebpackPlugin(),
    new MiniCssExtractPlugin({
      filename: "css/[name].css",
    }),
    new PurgeCSSPlugin({
      paths: glob.sync(`${path.join(__dirname, ".")}/**/*`, { nodir: true }),
      safelist: {
        standard: [/^bg-/, /^text-/], // Adjust this according to your needs
      },
    }),
    {
      apply: (compiler) => {
        compiler.hooks.afterEmit.tap("ZipOutputPlugin", (compilation) => {
          const outputDir = path.resolve(__dirname, "smart-link");
          const zipFile = path.resolve(__dirname, "smart-link.zip");

          const output = fs.createWriteStream(zipFile);
          const archive = archiver("zip", {
            zlib: { level: 9 },
          });

          output.on("close", function () {
            console.log(archive.pointer() + " total bytes");
            console.log(
              "archiver has been finalized and the output file descriptor has closed."
            );
          });

          archive.on("error", function (err) {
            throw err;
          });

          archive.pipe(output);
          archive.directory(outputDir, false);
          archive.finalize();
        });
      },
    },
  ],
  optimization: {
    minimize: true,
  },
  mode: "production",
};
