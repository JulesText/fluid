(() => {
  const app = Application("DEVONthink 3");
	app.includeStandardAdditions = true;
  let db = app.databases["Personal"];
  db.records().forEach( r =>  {
  	if (r.type() === "group") {
    	console.log(r.name());
			app.displayDialog(`group ${r.name()}`,
       {withTitle: "result"});
  	}
	})
})()
