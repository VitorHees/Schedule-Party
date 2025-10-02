const NAME = "Surname_Name_class";

const zl = require("zip-lib");

// Add folder app, database, resources and routes to zip
const zip = new zl.Zip();
zip.addFolder("app", "app");
zip.addFolder("database", "database");
zip.addFolder("resources", "resources");
zip.addFolder("routes", "routes");
zip.archive(`./${NAME}.zip`)
    .then(() => console.log(`Upload the file ${NAME}.zip to canvas.`))
    .catch(err => console.error(err));
