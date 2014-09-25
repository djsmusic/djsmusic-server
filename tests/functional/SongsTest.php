<?php

class SongsTest extends LocalWebTestCase
{

	public function canGetSongs(){
        
        $this->client->get('/music');

        $this->assertEquals(200, $this->client->response->status());
        $this->assertEquals('application/json', $this->client->response['Content-Type']);

        $songs = json_decode($this->client->response->body());

        $this->assertEquals(1, count($songs));
    }

    public function testSongDoesntExist(){
    	$this->client->get('/music/100');
        $this->assertEquals(404, $this->client->response->status());
        $this->assertEquals('application/json', $this->client->response['Content-Type']);
    }

    public function testSongExists(){

    	$this->client->get('/music/1');
        $this->assertEquals(200, $this->client->response->status());
        $this->assertEquals('application/json', $this->client->response['Content-Type']);

        $songData = json_decode($this->client->response->body());

        $this->assertSame("Demo Song", $songData->track->name);
    }

    public function canGetRating(){
        
        $this->client->get('/music/1/rating');
        $this->assertEquals(200, $this->client->response->status());
        $this->assertEquals('application/json', $this->client->response['Content-Type']);

        $rating = json_decode($this->client->response->body());

        $this->assertEquals(0, $rating['rating']);
    }

    public function cantPostRatingUnauthorized(){

    	$parameters = array('rating' => 5);
        
        $this->client->post('/music/1/rating', $parameters);
        $this->assertEquals(401, $this->client->response->status());
        $this->assertEquals('application/json', $this->client->response['Content-Type']);
    }
}