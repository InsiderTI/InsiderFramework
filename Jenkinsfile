pipeline {
    agent any

    stages {
        stage ("Checkout") {
            steps{
                checkout scm
            }
        }
        //stage ("Build") {
        //    steps{
        //        sh 'docker-compose build'
        //    }
        //}
        stage ("Deploy") {
            steps{
                sh 'docker-compose up -d'
                sh "docker exec -t kube-apiserver 'kubectl apply -f k8s_app.yaml'"
                sh "docker exec -t kube-apiserver 'kubectl set image deployment app app=test --record'"
                sh "docker exec -t kube-apiserver 'kubectl rollout status deployment/app'"
            }
        }
    }
} 
