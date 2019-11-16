node {
    stage "Checkout"
        sh "sudo mkdir -p /var/www/"
        sh "sudo chmod 777 -R /var/www/"
        dir('/var/www/insiderframework-site'){
            checkout scm
            // Pega o commit id para ser usado de tag (versionamento) na imagem
            sh "git rev-parse --short HEAD > commit-id"
            tag = readFile('commit-id').replace("\n", "").replace("\r", "")
            
            // Configura o nome da aplicação, o endereço do repositório e o nome da imagem com a versão
            appName = "app"
            registryHost = "127.0.0.1:30400/"
            imageName = "${registryHost}${appName}:${tag} docker/insider_framework-site.dockerfile"
        }
    
    // Configuramos os estágios
    stage "Build"
        def customImage = docker.build("${imageName}")
    stage "Push"
        customImage.push() 
    stage "Deploy PROD"
        input "Deploy to PROD?"
        customImage.push('latest')
        sh "kubectl apply -f https://raw.githubusercontent.com/InsiderTI/InsiderFramework-site/master/k8s_app.yaml"
        sh "kubectl set image deployment app app=${imageName} --record"
        sh "kubectl rollout status deployment/app"
} 
