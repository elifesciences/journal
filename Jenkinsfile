elifePipeline {
    stage 'Checkout'
    checkout scm
    def commit = elifeGitRevision()

    stage 'Project tests'
    lock('journal--ci') {
        builderDeployRevision 'journal--ci', commit
        builderProjectTests 'journal--ci', '/srv/journal'
        def phpunitTestArtifact = "${env.BUILD_TAG}.phpunit.xml"
        builderTestArtifact phpunitTestArtifact, 'journal--ci', '/srv/journal/build/phpunit.xml'
        def behatTestArtifact = "${env.BUILD_TAG}.behat.xml"
        builderTestArtifact behatTestArtifact, 'journal--ci', '/srv/journal/build/behat.xml'
        elifeVerifyJunitXml phpunitTestArtifact
        elifeVerifyJunitXml behatTestArtifact
    }

    elifeMainlineOnly {
        stage 'Deploy on end2end'
        builderDeployRevision 'journal--end2end', commit

        stage 'Deploy on demo'
        builderDeployRevision 'journal--demo', commit

        stage 'Approval'
        elifeGitMoveToBranch commit, 'approved'
    }
}
