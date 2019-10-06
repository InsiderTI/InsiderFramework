// Restify e bibliotecas globais padrão
import express from 'express';
import bodyParser from 'body-parser';
import methodOverride from 'method-override';

import fs from 'fs';
import path from 'path';
import rtrim from 'rtrim';
import magicglobals from 'magic-globals';

// Biblicoteca waitUntil usada em várias partes do código
import waitUntil from 'wait-until';

// Função InArray para javascript
import inArray from 'in-array';

// NodeMailer para possíveis envios de email
import nodemailer from 'nodemailer';

// Helmet para melhorar a segurança do código como um todo
import helmet from 'helmet';

// MD5 usada em algumas partes do código
import md5 from 'md5';

// Função htmlspecialchars usada em algumas partes do código
import htmlspecialchars from 'htmlspecialchars';

// Carregando as configurações
import configLoader from './config/config_loader';
// Importando todas as KeyClasses
import KcError from './keyclasses/js/error';
import KcFiletree from './keyclasses/js/filetree';
import KcJson from './keyclasses/js/json';
import KcMail from './keyclasses/js/mail';
import KcRoute from './keyclasses/js/route';

global.md5 = md5;
global.htmlspecialchars = htmlspecialchars;
global.inArray = inArray;
global.nodemailer = nodemailer;
global.configLoader = configLoader;
global.express = express;
global.fs = fs;
global.path = path;
global.rtrim = rtrim;
global.magicglobals = magicglobals;
global.nodemailer = nodemailer;
// Roteador do express
const router = express.Router();
global.router = router;
global.KcError = KcError;
global.KcFiletree = KcFiletree;
global.KcJson = KcJson;
global.KcMail = KcMail;
global.KcRoute = KcRoute;
global.waitUntil = waitUntil;

// Criando instância Express
const app = express();
app.use(helmet());
global.app = app;

// Carregando as rotas do sistema
KcRoute.ReadControllerRoutes();

// Interceptando requests com router personalizado
router.use((req, res, next) => {
  KcRoute.getURLRoute(req, res, next);
});
app.use('/', router);

// Definindo o manipulador de erros personalizado
app.use(bodyParser());
app.use(methodOverride());
app.use(KcError.InternalErrorHandler);

// Inicialização do servidor
const port = 3000;
app.listen(port, () => {
  console.log(`Server running on port \x1b[32m${port}\x1b[0m`);
});
